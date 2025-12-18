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
      console.warn('[ILO-IGA Mapping] Chat panel not available');
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
   * Get syllabus ID
   */
  function getSyllabusId() {
    const el = document.getElementById('syllabus-document');
    return el ? el.getAttribute('data-syllabus-id') : null;
  }

  /**
   * Parse JSON output from AI response
   * Expected format: { iga_columns: [...], mappings: [...] }
   * Can also return: { error: "error message" }
   */
  function parseILOIGAMappingJson(responseText) {
    try {
      if (!responseText || typeof responseText !== 'string') {
        throw new Error(`Invalid input: expected string, got ${typeof responseText}`);
      }

      console.log('[ILO-IGA Mapping] Attempting to parse response:', responseText.substring(0, 200));

      // Extract JSON from markdown code block if present
      let jsonText = responseText;
      
      // Try to find ```json...``` or ```...``` blocks
      const jsonBlockMatch = responseText.match(/```(?:json)?\s*([\s\S]*?)```/);
      if (jsonBlockMatch) {
        jsonText = jsonBlockMatch[1].trim();
        console.log('[ILO-IGA Mapping] Extracted from code block');
      } else {
        // If no code block, try to find raw JSON starting with { or [
        const rawJsonMatch = responseText.match(/(\{[\s\S]*\})/);
        if (rawJsonMatch) {
          jsonText = rawJsonMatch[1].trim();
          console.log('[ILO-IGA Mapping] Extracted raw JSON');
        }
      }

      if (!jsonText || jsonText.length === 0) {
        throw new Error('No JSON content found in response');
      }

      console.log('[ILO-IGA Mapping] JSON text to parse:', jsonText.substring(0, 200));

      const parsed = JSON.parse(jsonText);

      // Check for error response
      if (parsed.error) {
        console.warn('[ILO-IGA Mapping] AI returned error:', parsed.error);
        throw new Error(parsed.error);
      }

      // Validate structure
      if (!Array.isArray(parsed.mappings)) {
        throw new Error('Invalid JSON structure: missing mappings array');
      }

      console.log(`[ILO-IGA Mapping] Found ${parsed.mappings.length} mappings`);

      // Validate iga_columns if present
      if (parsed.iga_columns && !Array.isArray(parsed.iga_columns)) {
        throw new Error('iga_columns must be an array');
      }

      // Validate each mapping row
      parsed.mappings.forEach((mapping, idx) => {
        if (!mapping.ilo_text || typeof mapping.ilo_text !== 'string') {
          throw new Error(`Mapping ${idx}: missing or invalid ilo_text`);
        }
        if (!mapping.igas || typeof mapping.igas !== 'object' || Array.isArray(mapping.igas)) {
          throw new Error(`Mapping ${idx}: igas must be an object`);
        }
        if (typeof mapping.position !== 'number') {
          throw new Error(`Mapping ${idx}: position must be a number`);
        }
      });

      console.log('[ILO-IGA Mapping] Parsed JSON successfully', parsed);
      return parsed;

    } catch (error) {
      console.error('[ILO-IGA Mapping] JSON parsing error:', error);
      console.error('[ILO-IGA Mapping] Response text:', responseText);
      throw new Error(`Failed to parse ILO-IGA mapping JSON: ${error.message}`);
    }
  }

  /**
   * Save ILO-IGA mappings to database
   */
  async function saveILOIGAMappings(parsedData) {
    try {
      const syllabusId = getSyllabusId();
      if (!syllabusId) {
        throw new Error('Syllabus ID not found');
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
      if (!csrfToken) {
        throw new Error('CSRF token not found');
      }

      console.log('[ILO-IGA Mapping] Saving mappings:', parsedData);

      // Extract mappings and IGA columns from parsed data
      const mappings = parsedData.mappings || [];
      const iga_columns = parsedData.iga_columns || [];

      const response = await fetch(`/faculty/syllabus/save-ilo-iga-mapping`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ 
          syllabus_id: syllabusId,
          mappings: mappings,
          iga_columns: iga_columns
        })
      });

      console.log('[ILO-IGA Mapping] Response status:', response.status);
      
      const data = await response.json();
      console.log('[ILO-IGA Mapping] Response data:', data);

      if (!response.ok) {
        console.error('[ILO-IGA Mapping] Server error response:', data);
        throw new Error(data.message || `HTTP Error: ${response.status}`);
      }

      if (!data.success) {
        console.error('[ILO-IGA Mapping] Save failed:', data);
        throw new Error(data.message || 'Failed to save mappings');
      }

      console.log('[ILO-IGA Mapping] Mappings saved successfully', data);
      return data;

    } catch (error) {
      console.error('[ILO-IGA Mapping] Save error:', error);
      console.error('[ILO-IGA Mapping] Error stack:', error.stack);
      throw error;
    }
  }

  /**
   * Generate ILO-IGA mapping using AI
   */
  async function generateILOIGAMapping() {
    if (_inFlight) {
      console.warn('[ILO-IGA Mapping] Already processing');
      return false;
    }

    _inFlight = true;

    try {
      initChatPanel();

      // Show loading message
      const loadingMsg = appendChatMessage('ai', 'Processing ILO-IGA mapping...', true);

      // Prepare message for AI - do not include snapshots in message body
      // SVAI.send will automatically collect snapshots via AIController
      const message = `Generate ILO-IGA mapping for this syllabus.`;

      console.log('[ILO-IGA Mapping] Sending request to AI...');

      // Send to AI using SVAI module
      if (!window.SVAI || typeof window.SVAI.send !== 'function') {
        throw new Error('AI module (window.SVAI) not available');
      }

      const response = await window.SVAI.send(message, []);

      console.log('[ILO-IGA Mapping] AI Response:', response);

      // Parse response
      const parsedData = parseILOIGAMappingJson(response);
      console.log('[ILO-IGA Mapping] Parsed data:', parsedData);

      // Save to database
      try {
        console.log('[ILO-IGA Mapping] Attempting to save mappings...');
        await saveILOIGAMappings(parsedData);
      } catch (saveError) {
        console.error('[ILO-IGA Mapping] Save failed:', saveError);
        throw new Error(`Failed to save mappings: ${saveError.message}`);
      }

      // Remove loading message
      if (loadingMsg) {
        loadingMsg.remove();
      }

      // Show completion message in chat
      appendChatMessage('ai', 'Mapping ILO-IGA Done');

      console.log('[ILO-IGA Mapping] Mapping generated and saved successfully');

      // Refresh the partial immediately with the new data
      if (window.refreshIloIgaPartial && typeof window.refreshIloIgaPartial === 'function') {
        console.log('[ILO-IGA Mapping] Refreshing partial with new data');
        window.refreshIloIgaPartial(parsedData.iga_columns || [], parsedData.mappings || []);
      } else {
        console.warn('[ILO-IGA Mapping] refreshIloIgaPartial function not available');
      }

      return true;

    } catch (error) {
      console.error('[ILO-IGA Mapping] Error:', error);

      // Show error message in chat
      appendChatMessage('ai', `Error: ${error.message || 'Failed to generate ILO-IGA mapping'}`);

      return false;

    } finally {
      _inFlight = false;
    }
  }

  /**
   * Public API
   */
  window.SVILOIGAMapping = {
    generate: generateILOIGAMapping
  };

  console.log('[ILO-IGA Mapping] Module initialized');

})();
