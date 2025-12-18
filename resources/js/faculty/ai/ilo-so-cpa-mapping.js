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
      console.warn('[ILO-SO-CPA Mapping] Chat panel not available');
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
   * Reload the ILO-SO-CPA mapping table via AJAX
   */
  function reloadILOSOCPAMappingTable() {
    try {
      const syllabusId = getSyllabusId();
      if (!syllabusId) {
        console.warn('[ILO-SO-CPA Mapping] Syllabus ID not found');
        return;
      }

      // Trigger any existing reload function if available
      if (window.reloadILOSOCPAMappings && typeof window.reloadILOSOCPAMappings === 'function') {
        console.log('[ILO-SO-CPA Mapping] Reloading table using global function');
        window.reloadILOSOCPAMappings();
      } else {
        console.warn('[ILO-SO-CPA Mapping] No reload function available');
      }
    } catch (error) {
      console.error('[ILO-SO-CPA Mapping] Error reloading table:', error);
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
      console.warn('[ILO-SO-CPA Mapping] Failed to collect snapshots', e);
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
      console.warn('[ILO-SO-CPA Mapping] Failed to collect all prompts', e);
      return '{}';
    }
  }

  /**
   * Parse JSON output from AI response
   * Expected format: { mappings: [...] }
   */
  function parseILOSOCPAMappingJson(responseText) {
    try {
      if (!responseText || typeof responseText !== 'string') {
        throw new Error(`Invalid input: expected string, got ${typeof responseText}`);
      }

      console.log('[ILO-SO-CPA Mapping] Attempting to parse response:', responseText.substring(0, 200));

      // Extract JSON from markdown code block if present
      let jsonText = responseText;
      
      // Try to find ```json...``` or ```...``` blocks
      const jsonBlockMatch = responseText.match(/```(?:json)?\s*([\s\S]*?)```/);
      if (jsonBlockMatch) {
        jsonText = jsonBlockMatch[1].trim();
        console.log('[ILO-SO-CPA Mapping] Extracted from code block');
      } else {
        // If no code block, try to find raw JSON starting with { or [
        const rawJsonMatch = responseText.match(/(\{[\s\S]*\})/);
        if (rawJsonMatch) {
          jsonText = rawJsonMatch[1].trim();
          console.log('[ILO-SO-CPA Mapping] Extracted raw JSON');
        }
      }

      if (!jsonText || jsonText.length === 0) {
        throw new Error('No JSON content found in response');
      }

      console.log('[ILO-SO-CPA Mapping] JSON text to parse:', jsonText.substring(0, 200));

      const parsed = JSON.parse(jsonText);

      // Validate structure
      if (!Array.isArray(parsed.mappings)) {
        throw new Error('Invalid JSON structure: missing mappings array');
      }

      if (parsed.mappings.length === 0) {
        throw new Error('Mappings array is empty');
      }

      console.log(`[ILO-SO-CPA Mapping] Found ${parsed.mappings.length} mappings`);

      // Validate so_columns if present
      if (parsed.so_columns && !Array.isArray(parsed.so_columns)) {
        throw new Error('so_columns must be an array');
      }

      // Validate structure
      if (!Array.isArray(parsed.mappings) || parsed.mappings.length === 0) {
        throw new Error('Mappings array is missing or empty');
      }

      // Validate each mapping row
      parsed.mappings.forEach((mapping, idx) => {
        if (!mapping.ilo_text || typeof mapping.ilo_text !== 'string') {
          throw new Error(`Mapping ${idx}: missing or invalid ilo_text`);
        }
        if (!mapping.sos || typeof mapping.sos !== 'object' || Array.isArray(mapping.sos)) {
          throw new Error(`Mapping ${idx}: sos must be an object`);
        }
        // c, p, a can be string or null
        if (mapping.c !== null && typeof mapping.c !== 'string') {
          throw new Error(`Mapping ${idx}: c must be string or null (got ${typeof mapping.c})`);
        }
        if (mapping.p !== null && typeof mapping.p !== 'string') {
          throw new Error(`Mapping ${idx}: p must be string or null (got ${typeof mapping.p})`);
        }
        if (mapping.a !== null && typeof mapping.a !== 'string') {
          throw new Error(`Mapping ${idx}: a must be string or null (got ${typeof mapping.a})`);
        }
        if (typeof mapping.position !== 'number') {
          throw new Error(`Mapping ${idx}: position must be a number`);
        }
      });

      console.log('[ILO-SO-CPA Mapping] Parsed JSON successfully', parsed);
      return parsed;

    } catch (error) {
      console.error('[ILO-SO-CPA Mapping] JSON parsing error:', error);
      console.error('[ILO-SO-CPA Mapping] Response text:', responseText);
      throw new Error(`Failed to parse ILO-SO-CPA mapping JSON: ${error.message}`);
    }
  }

  /**
   * Save ILO-SO-CPA mappings to database
   */
  async function saveILOSOCPAMappings(parsedData) {
    try {
      const syllabusId = getSyllabusId();
      if (!syllabusId) {
        throw new Error('Syllabus ID not found');
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
      if (!csrfToken) {
        throw new Error('CSRF token not found');
      }

      console.log('[ILO-SO-CPA Mapping] Saving mappings:', parsedData);

      // Extract mappings and SO columns from parsed data
      const mappings = parsedData.mappings || [];
      const so_columns = parsedData.so_columns || [];

      const response = await fetch(`/faculty/syllabus/save-ilo-so-cpa-mapping`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ 
          syllabus_id: syllabusId,
          mappings: mappings,
          so_columns: so_columns
        })
      });

      console.log('[ILO-SO-CPA Mapping] Response status:', response.status);
      
      const data = await response.json();
      console.log('[ILO-SO-CPA Mapping] Response data:', data);

      if (!response.ok) {
        console.error('[ILO-SO-CPA Mapping] Server error response:', data);
        throw new Error(data.message || `HTTP Error: ${response.status}`);
      }

      if (!data.success) {
        console.error('[ILO-SO-CPA Mapping] Save failed:', data);
        throw new Error(data.message || 'Failed to save mappings');
      }

      console.log('[ILO-SO-CPA Mapping] Mappings saved successfully', data);
      return data;

    } catch (error) {
      console.error('[ILO-SO-CPA Mapping] Save error:', error);
      console.error('[ILO-SO-CPA Mapping] Error stack:', error.stack);
      throw error;
    }
  }

  /**
   * Generate ILO-SO-CPA mapping using AI
   */
  async function generateILOSOCPAMapping() {
    if (_inFlight) {
      console.warn('[ILO-SO-CPA Mapping] Already processing');
      return false;
    }

    _inFlight = true;

    try {
      initChatPanel();

      // Show loading message
      const loadingMsg = appendChatMessage('ai', 'Processing ILO-SO-CPA mapping...', true);

      // Prepare message for AI - do not include snapshots in message body
      // SVAI.send will automatically collect snapshots via AIController
      const message = `Generate ILO-SO-CPA mapping for this syllabus.`;

      console.log('[ILO-SO-CPA Mapping] Sending request to AI...');

      // Send to AI using SVAI module
      if (!window.SVAI || typeof window.SVAI.send !== 'function') {
        throw new Error('AI module (window.SVAI) not available');
      }

      const response = await window.SVAI.send(message, []);

      console.log('[ILO-SO-CPA Mapping] AI Response:', response);

      // Parse response
      const parsedData = parseILOSOCPAMappingJson(response);
      console.log('[ILO-SO-CPA Mapping] Parsed data:', parsedData);

      // Save to database
      try {
        console.log('[ILO-SO-CPA Mapping] Attempting to save mappings...');
        await saveILOSOCPAMappings(parsedData);
      } catch (saveError) {
        console.error('[ILO-SO-CPA Mapping] Save failed:', saveError);
        throw new Error(`Failed to save mappings: ${saveError.message}`);
      }

      // Remove loading message
      if (loadingMsg) {
        loadingMsg.remove();
      }

      // Show completion message in chat
      appendChatMessage('ai', 'Mapping ILO-SO-CPA Done');

      console.log('[ILO-SO-CPA Mapping] Mapping generated and saved successfully');

      // Refresh the partial immediately with the new data
      if (window.refreshIloSoCpaPartial && typeof window.refreshIloSoCpaPartial === 'function') {
        console.log('[ILO-SO-CPA Mapping] Refreshing partial with new data');
        window.refreshIloSoCpaPartial(parsedData.so_columns || [], parsedData.mappings || []);
      } else {
        console.warn('[ILO-SO-CPA Mapping] refreshIloSoCpaPartial function not available');
        // Fallback to reload function if available
        reloadILOSOCPAMappingTable();
      }

      return true;

    } catch (error) {
      console.error('[ILO-SO-CPA Mapping] Error:', error);

      // Show error message in chat
      appendChatMessage('ai', `Error: ${error.message || 'Failed to generate ILO-SO-CPA mapping'}`);

      return false;

    } finally {
      _inFlight = false;
    }
  }

  /**
   * Public API
   */
  window.SVILOSOCPAMapping = {
    generate: generateILOSOCPAMapping
  };

  console.log('[ILO-SO-CPA Mapping] Module initialized');

})();
