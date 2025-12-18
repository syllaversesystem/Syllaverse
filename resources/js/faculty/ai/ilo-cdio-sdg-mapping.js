(function(){
  'use strict';

  let _chatPanel = null;
  let _inFlight = false;

  /**
   * Initialize chat panel reference
   */
  function initChatPanel() {
    if (!_chatPanel) {
      _chatPanel = document.getElementById('aiChatMessages');
    }
    return _chatPanel;
  }

  /**
   * Append message to chat
   */
  function appendChatMessage(role, text, isLoading = false) {
    const chatPanel = initChatPanel();
    if (!chatPanel) {
      console.warn('[ILO-CDIO-SDG Mapping] Chat panel not available');
      return null;
    }

    const msgDiv = document.createElement('div');
    msgDiv.className = `ai-chat-msg ${role}${isLoading ? ' loading' : ''}`;

    const bubbleDiv = document.createElement('div');
    bubbleDiv.className = 'ai-chat-bubble';
    bubbleDiv.textContent = text;

    msgDiv.appendChild(bubbleDiv);
    chatPanel.appendChild(msgDiv);

    // Scroll to bottom
    chatPanel.scrollTop = chatPanel.scrollHeight;

    return msgDiv;
  }

  /**
   * Reload the ILO-CDIO-SDG mapping table via AJAX
   */
  function reloadILOCDIOSDGMappingTable() {
    try {
      const syllabusId = getSyllabusId();
      if (!syllabusId) {
        console.warn('[ILO-CDIO-SDG Mapping] Syllabus ID not found');
        return;
      }

      // Trigger any existing reload function if available
      if (window.reloadILOCDIOSDGMappings && typeof window.reloadILOCDIOSDGMappings === 'function') {
        console.log('[ILO-CDIO-SDG Mapping] Reloading table using global function');
        window.reloadILOCDIOSDGMappings();
      } else {
        console.warn('[ILO-CDIO-SDG Mapping] No reload function available');
      }
    } catch (error) {
      console.error('[ILO-CDIO-SDG Mapping] Error reloading table:', error);
    }
  }

  /**
   * Get syllabus ID
   */
  function getSyllabusId() {
    const el = document.getElementById('syllabus-document');
    return el ? el.getAttribute('data-syllabus-id') : null;
  }

  /**
   * Collect all snapshots
   */
  function collectAllSnapshots() {
    if (!window.SVSnapshot || typeof window.SVSnapshot.collectAllSnapshots !== 'function') {
      return '{}';
    }
    try {
      const snapshots = window.SVSnapshot.collectAllSnapshots();
      return JSON.stringify(snapshots);
    } catch (e) {
      console.warn('[ILO-CDIO-SDG Mapping] Failed to collect snapshots', e);
      return '{}';
    }
  }

  /**
   * Collect all prompts
   */
  function collectAllPrompts() {
    if (!window.SVPrompts || typeof window.SVPrompts.getAll !== 'function') {
      return '{}';
    }
    try {
      const allPrompts = window.SVPrompts.getAll();
      return JSON.stringify(allPrompts);
    } catch (e) {
      console.warn('[ILO-CDIO-SDG Mapping] Failed to collect all prompts', e);
      return '{}';
    }
  }

  /**
   * Parse JSON output from AI response
   * Expected format: { cdio_columns: [...], sdg_columns: [...], mappings: [...] }
   */
  function parseILOCDIOSDGMappingJson(responseText) {
    try {
      if (!responseText || typeof responseText !== 'string') {
        throw new Error(`Invalid input: expected string, got ${typeof responseText}`);
      }

      console.log('[ILO-CDIO-SDG Mapping] Attempting to parse response:', responseText.substring(0, 200));

      // Extract JSON from markdown code block if present
      let jsonText = responseText;
      
      // Try to find ```json...``` or ```...``` blocks
      const jsonBlockMatch = responseText.match(/```(?:json)?\s*([\s\S]*?)```/);
      if (jsonBlockMatch) {
        jsonText = jsonBlockMatch[1].trim();
        console.log('[ILO-CDIO-SDG Mapping] Extracted from code block');
      } else {
        // If no code block, try to find raw JSON starting with { or [
        const rawJsonMatch = responseText.match(/(\{[\s\S]*\})/);
        if (rawJsonMatch) {
          jsonText = rawJsonMatch[1].trim();
          console.log('[ILO-CDIO-SDG Mapping] Extracted raw JSON');
        }
      }

      if (!jsonText || jsonText.length === 0) {
        throw new Error('No JSON content found in response');
      }

      console.log('[ILO-CDIO-SDG Mapping] JSON text to parse:', jsonText.substring(0, 200));

      const parsed = JSON.parse(jsonText);

      // If it's an explicit error response from the model, return it for higher-level handling
      if (parsed.error) {
        console.warn('[ILO-CDIO-SDG Mapping] AI returned error payload:', parsed.error);
        return { error: parsed.error };
      }

      // Validate structure
      if (!Array.isArray(parsed.mappings)) {
        throw new Error('Invalid JSON structure: missing mappings array');
      }

      // Allow empty mappings array
      if (parsed.mappings.length === 0) {
        console.warn('[ILO-CDIO-SDG Mapping] Mappings array is empty (no alignments)');
      }

      console.log(`[ILO-CDIO-SDG Mapping] Found ${parsed.mappings.length} mappings`);

      // Validate cdio_columns if present
      if (parsed.cdio_columns && !Array.isArray(parsed.cdio_columns)) {
        throw new Error('cdio_columns must be an array');
      }

      // Validate sdg_columns if present
      if (parsed.sdg_columns && !Array.isArray(parsed.sdg_columns)) {
        throw new Error('sdg_columns must be an array');
      }

      // Validate each mapping row
      parsed.mappings.forEach((mapping, idx) => {
        if (!mapping.ilo_text || typeof mapping.ilo_text !== 'string') {
          throw new Error(`Mapping ${idx}: missing or invalid ilo_text`);
        }
        if (!mapping.cdios || typeof mapping.cdios !== 'object' || Array.isArray(mapping.cdios)) {
          throw new Error(`Mapping ${idx}: cdios must be an object`);
        }
        if (!mapping.sdgs || typeof mapping.sdgs !== 'object' || Array.isArray(mapping.sdgs)) {
          throw new Error(`Mapping ${idx}: sdgs must be an object`);
        }
        if (typeof mapping.position !== 'number') {
          throw new Error(`Mapping ${idx}: position must be a number`);
        }
      });

      console.log('[ILO-CDIO-SDG Mapping] Parsed JSON successfully', parsed);
      return parsed;

    } catch (error) {
      console.error('[ILO-CDIO-SDG Mapping] JSON parsing error:', error);
      console.error('[ILO-CDIO-SDG Mapping] Response text:', responseText);
      throw new Error(`Failed to parse ILO-CDIO-SDG mapping JSON: ${error.message}`);
    }
  }

  /**
   * Save ILO-CDIO-SDG mappings to database
   */
  async function saveILOCDIOSDGMappings(parsedData) {
    try {
      const syllabusId = getSyllabusId();
      if (!syllabusId) {
        throw new Error('Syllabus ID not found');
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
      if (!csrfToken) {
        throw new Error('CSRF token not found');
      }

      console.log('[ILO-CDIO-SDG Mapping] Saving mappings:', parsedData);

      // Extract mappings, CDIO columns, and SDG columns from parsed data
      const mappings = parsedData.mappings || [];
      const cdio_columns = parsedData.cdio_columns || [];
      const sdg_columns = parsedData.sdg_columns || [];

      const response = await fetch(`/faculty/syllabus/save-ilo-cdio-sdg-mapping`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ 
          syllabus_id: syllabusId,
          mappings: mappings,
          cdio_columns: cdio_columns,
          sdg_columns: sdg_columns
        })
      });

      console.log('[ILO-CDIO-SDG Mapping] Response status:', response.status);
      
      const data = await response.json();
      console.log('[ILO-CDIO-SDG Mapping] Response data:', data);

      if (!response.ok) {
        console.error('[ILO-CDIO-SDG Mapping] Server error response:', data);
        throw new Error(data.message || `HTTP Error: ${response.status}`);
      }

      if (!data.success) {
        console.error('[ILO-CDIO-SDG Mapping] Save failed:', data);
        throw new Error(data.message || 'Failed to save mappings');
      }

      console.log('[ILO-CDIO-SDG Mapping] Mappings saved successfully', data);
      return data;

    } catch (error) {
      console.error('[ILO-CDIO-SDG Mapping] Save error:', error);
      console.error('[ILO-CDIO-SDG Mapping] Error stack:', error.stack);
      throw error;
    }
  }

  /**
   * Generate ILO-CDIO-SDG mapping using AI
   */
  async function generateILOCDIOSDGMapping() {
    if (_inFlight) {
      console.warn('[ILO-CDIO-SDG Mapping] Already processing');
      return false;
    }

    _inFlight = true;

    try {
      initChatPanel();

      // Show loading message
      const loadingMsg = appendChatMessage('ai', 'Processing ILO-CDIO-SDG mapping...', true);

      // Prepare message for AI - do not include snapshots in message body
      // SVAI.send will automatically collect snapshots via AIController
      const message = `Generate ILO-CDIO-SDG mapping for this syllabus.`;

      console.log('[ILO-CDIO-SDG Mapping] Sending request to AI...');

      // Send to AI using SVAI module
      if (!window.SVAI || typeof window.SVAI.send !== 'function') {
        throw new Error('AI module (window.SVAI) not available');
      }

      const response = await window.SVAI.send(message, []);

      console.log('[ILO-CDIO-SDG Mapping] AI Response:', response);

      // Parse response
      const parsedData = parseILOCDIOSDGMappingJson(response);
      console.log('[ILO-CDIO-SDG Mapping] Parsed data:', parsedData);

      // If AI returned a structured error payload, surface it gracefully and stop
      if (parsedData && parsedData.error) {
        if (loadingMsg) loadingMsg.remove();
        appendChatMessage('ai', parsedData.error);
        return false;
      }

      // Save to database
      try {
        console.log('[ILO-CDIO-SDG Mapping] Attempting to save mappings...');
        await saveILOCDIOSDGMappings(parsedData);
      } catch (saveError) {
        console.error('[ILO-CDIO-SDG Mapping] Save failed:', saveError);
        throw new Error(`Failed to save mappings: ${saveError.message}`);
      }

      // Remove loading message
      if (loadingMsg) {
        loadingMsg.remove();
      }

      // Show completion message in chat
      appendChatMessage('ai', 'Mapping ILO-CDIO-SDG Done');

      console.log('[ILO-CDIO-SDG Mapping] Mapping generated and saved successfully');

      // Refresh the partial immediately with the new data
      if (window.refreshIloCdioSdgPartial && typeof window.refreshIloCdioSdgPartial === 'function') {
        console.log('[ILO-CDIO-SDG Mapping] Refreshing partial with new data');
        window.refreshIloCdioSdgPartial(parsedData.mappings || []);
      } else {
        console.warn('[ILO-CDIO-SDG Mapping] refreshIloCdioSdgPartial function not available');
        // Fallback to reload function if available
        reloadILOCDIOSDGMappingTable();
      }

      return true;

    } catch (error) {
      console.error('[ILO-CDIO-SDG Mapping] Error:', error);

      // Show error message in chat
      appendChatMessage('ai', `Error: ${error.message || 'Failed to generate ILO-CDIO-SDG mapping'}`);

      return false;

    } finally {
      _inFlight = false;
    }
  }

  /**
   * Public API
   */
  window.SVILOCDIOSDGMapping = {
    generate: generateILOCDIOSDGMapping
  };

  console.log('[ILO-CDIO-SDG Mapping] Module initialized');

})();
