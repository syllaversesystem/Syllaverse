/**
 * File: resources/js/faculty/ai/chat-panel.js
 * Description: AI Chat Panel - modern slide-in interface with message handling
 */

(function() {
  'use strict';

  // State
  let isOpen = false;
  const conversationHistory = [];

  // DOM Elements
  let fab, panel, backdrop, closeBtn, messagesContainer, input, sendBtn;

  /**
   * Initialize the chat panel
   */
  function init() {
    // Get DOM references
    fab = document.getElementById('aiChatFab');
    panel = document.getElementById('aiChatPanel');
    backdrop = document.getElementById('aiChatBackdrop');
    closeBtn = document.getElementById('aiChatClose');
    messagesContainer = document.getElementById('aiChatMessages');
    input = document.getElementById('aiChatInput');
    sendBtn = document.getElementById('aiChatSend');

    if (!fab || !panel) {
      console.warn('[AI Chat] Required elements not found');
      return;
    }

    // Attach event listeners
    fab.addEventListener('click', openPanel);
    closeBtn?.addEventListener('click', closePanel);
    backdrop?.addEventListener('click', closePanel);
    sendBtn?.addEventListener('click', handleSend);
    
    input?.addEventListener('input', handleInputResize);
    input?.addEventListener('keydown', handleKeyDown);

    // Action panel buttons
    initActionButtons();

    // Draggable and resizable
    initDragAndResize();

    // Initial resize
    if (input) handleInputResize();

    console.log('[AI Chat] Initialized');
  }

  /**
   * Initialize drag and resize functionality
   */
  function initDragAndResize() {
    const dragHandle = document.getElementById('aiChatDragHandle');
    const resizeHandle = document.getElementById('aiChatResizeHandle');
    
    let isDragging = false;
    let isResizing = false;
    let startX, startY, startWidth, startRight;

    // Dragging
    if (dragHandle) {
      dragHandle.addEventListener('mousedown', (e) => {
        // Prevent drag if clicking on buttons
        if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
          return;
        }

        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        
        panel.classList.add('dragging');
        dragHandle.classList.add('dragging');
      });
    }

    // Resizing
    if (resizeHandle) {
      resizeHandle.addEventListener('mousedown', (e) => {
        isResizing = true;
        startX = e.clientX;
        startWidth = panel.offsetWidth;
        startRight = parseInt(getComputedStyle(panel).right) || 0;
        
        resizeHandle.classList.add('resizing');
      });
    }

    // Mouse move for drag and resize
    document.addEventListener('mousemove', (e) => {
      if (isDragging && panel.classList.contains('open')) {
        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;
        
        const currentTop = parseInt(getComputedStyle(panel).top) || 0;
        const currentLeft = parseInt(getComputedStyle(panel).left) || 0;
        
        // Update position
        panel.style.top = (currentTop + deltaY) + 'px';
        panel.style.left = (currentLeft + deltaX) + 'px';
        panel.style.right = 'auto';
        panel.style.transform = 'none';
        
        startX = e.clientX;
        startY = e.clientY;
      }
      
      if (isResizing && panel.classList.contains('open')) {
        const deltaX = e.clientX - startX;
        const newWidth = Math.max(300, Math.min(startWidth - deltaX, window.innerWidth - 50));
        
        panel.style.width = newWidth + 'px';
      }
    });

    // Mouse up to stop drag/resize
    document.addEventListener('mouseup', () => {
      if (isDragging) {
        isDragging = false;
        panel.classList.remove('dragging');
        dragHandle?.classList.remove('dragging');
      }
      
      if (isResizing) {
        isResizing = false;
        resizeHandle?.classList.remove('resizing');
      }
    });

    // Prevent text selection during drag
    document.addEventListener('selectstart', (e) => {
      if (isDragging || isResizing) {
        e.preventDefault();
      }
    });
  }

  /**
   * Initialize action panel buttons
   */
  function initActionButtons() {
    const actionButtons = document.querySelectorAll('.ai-chip');
    
    actionButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const buttonText = this.textContent.trim();
        
        // Open panel if not already open
        if (!isOpen) {
          openPanel();
        }
        
        // Map button text to prompts
        let message = '';
        switch(buttonText) {
          case 'Course Rationale and Description':
            message = 'Generate a comprehensive course rationale and description';
            break;
          case 'Teaching, Learning, and Assessment Strategies':
            message = 'Generate teaching, learning, and assessment strategies';
            break;
          case 'ILO':
            message = 'Generate intended learning outcomes';
            break;
          case 'Teaching, Learning, and Assessment (TLA) Activities':
            message = 'Generate teaching, learning, and assessment activities';
            break;
          case 'Assessment Schedule':
            message = 'Generate an assessment schedule';
            break;
          case 'ILO-SO-CPA':
            message = 'Create ILO-SO-CPA mapping';
            break;
          case 'ILO-IGA':
            message = 'Create ILO-IGA mapping';
            break;
          case 'ILO-CDIO-SDG':
            message = 'Create ILO-CDIO-SDG mapping';
            break;
          default:
            message = buttonText;
        }
        
        // Send message
        if (message && input) {
          input.value = message;
          handleSend();
        }
      });
    });
  }

  /**
   * Open the chat panel
   */
  function openPanel() {
    if (isOpen) return;
    
    isOpen = true;
    panel.classList.add('open');
    backdrop.classList.add('show');
    panel.setAttribute('aria-hidden', 'false');
    
    // Reset to default position if not already positioned
    if (!panel.style.left && !panel.style.top) {
      panel.style.right = '0';
      panel.style.top = '0';
    }
    
    // Focus input
    setTimeout(() => input?.focus(), 300);
    
    // Scroll to bottom
    scrollToBottom();
  }

  /**
   * Close the chat panel
   */
  function closePanel() {
    if (!isOpen) return;
    
    isOpen = false;
    panel.classList.remove('open');
    backdrop.classList.remove('show');
    panel.setAttribute('aria-hidden', 'true');
  }

  /**
   * Handle keyboard shortcuts
   */
  function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    } else if (e.key === 'Escape') {
      closePanel();
    }
  }

  /**
   * Auto-resize textarea as user types
   */
  function handleInputResize() {
    if (!input) return;
    
    input.style.height = 'auto';
    const newHeight = Math.min(input.scrollHeight, 120);
    input.style.height = newHeight + 'px';
  }

  /**
   * Handle send button click
   */
  async function handleSend() {
    if (!input || !messagesContainer) return;
    
    const message = input.value.trim();
    if (!message) return;

    // Add user message to UI
    appendMessage('user', message);
    
    // Clear input
    input.value = '';
    handleInputResize();

    // Disable send button during processing
    if (sendBtn) {
      sendBtn.disabled = true;
    }

    // Add loading indicator
    const loadingMsg = appendMessage('ai', 'Thinking...', true);

    try {
      // Send via core AI module
      const response = await (window.SVAI ? window.SVAI.send(message, conversationHistory) : Promise.reject(new Error('SVAI not loaded')));
      
      // Remove loading
      if (loadingMsg) loadingMsg.remove();
      
      // Add AI response
      appendMessage('ai', response);
      
    } catch (error) {
      console.error('[AI Chat] Error:', error);
      
      // Remove loading
      if (loadingMsg) loadingMsg.remove();
      
      // Show error message
      appendMessage('ai', 'Sorry, I encountered an error. Please try again.');
    } finally {
      // Re-enable send button
      if (sendBtn) {
        sendBtn.disabled = false;
      }
      
      // Refocus input
      input?.focus();
    }
  }

  /**
   * Append message to chat
   */
  function appendMessage(role, text, isLoading = false) {
    if (!messagesContainer) return null;

    const msgDiv = document.createElement('div');
    msgDiv.className = `ai-chat-msg ${role}${isLoading ? ' loading' : ''}`;
    
    const bubbleDiv = document.createElement('div');
    bubbleDiv.className = 'ai-chat-bubble';
    
    if (role === 'ai' && !isLoading) {
      // Assessment Tasks: render plainly (no card), before other table handlers
      if (isAssessmentTasksResponse(text)) {
        bubbleDiv.innerHTML = formatAIResponse(text);

      // Check for TLA Activities first (tables can otherwise be misclassified)
      } else if (isTlaActivitiesResponse(text)) {
        const tlaTable = extractIloTable(text); // Reuse same table extraction

        // Extract intro by removing the markdown table from the text
        let introText = text;
        if (tlaTable) {
          // Remove markdown table pattern from text (lines with |)
          const lines = text.split('\n');
          const nonTableLines = [];
          let inTable = false;

          for (const line of lines) {
            if (line.trim().startsWith('|')) {
              inTable = true;
            } else if (inTable && line.trim() === '') {
              inTable = false;
            } else if (!inTable) {
              nonTableLines.push(line);
            }
          }

          introText = nonTableLines.join('\n').trim();
        }

        if (introText) {
          bubbleDiv.innerHTML = formatAIResponse(introText);
        }

        // Add a special card for TLA Activities with insert button
        if (tlaTable) {
          const containerDiv = document.createElement('div');
          containerDiv.className = 'ai-tla-activities-container';

          const cardDiv = document.createElement('div');
          cardDiv.className = 'ai-tla-activities-card';
          cardDiv.innerHTML = `
            <div class="ai-card-header">
              <span class="ai-card-title">Teaching, Learning, and Assessment (TLA) Activities</span>
            </div>
            <div class="ai-card-body">
              ${tlaTable}
            </div>
          `;

          const buttonDiv = document.createElement('div');
          buttonDiv.className = 'ai-card-button-wrapper';
          buttonDiv.innerHTML = `
            <button type="button" class="ai-card-insert-btn" title="Insert into syllabus" aria-label="Insert TLA Activities">
              <i class="bi bi-download me-2"></i>
              Insert into Syllabus
            </button>
          `;

          // Add click handler for insert button
          const insertBtn = buttonDiv.querySelector('.ai-card-insert-btn');
          insertBtn.addEventListener('click', () => {
            insertTlaActivities(tlaTable);
          });

          containerDiv.appendChild(cardDiv);
          containerDiv.appendChild(buttonDiv);
          bubbleDiv.appendChild(containerDiv);
        }
      // Check if this is a TLAS response
      } else if (isTlasResponse(text)) {
        const tlas = extractTlasContent(text);
        
        // Extract only the conversational intro (everything before the TLAS)
        const introText = extractConversationalIntro(text, tlas);
        if (introText) {
          bubbleDiv.innerHTML = formatAIResponse(introText);
        }
        
        // Add a special card for TLAS with insert button
        if (tlas) {
          const containerDiv = document.createElement('div');
          containerDiv.className = 'ai-tlas-container';
          
          const cardDiv = document.createElement('div');
          cardDiv.className = 'ai-tlas-card';
          cardDiv.innerHTML = `
            <div class="ai-card-header">
              <span class="ai-card-title">Teaching, Learning & Assessment Strategies</span>
            </div>
            <div class="ai-card-body">
              ${tlas.split('\n\n').map(para => `<p>${escapeHtml(para)}</p>`).join('')}
            </div>
          `;
          
          const buttonDiv = document.createElement('div');
          buttonDiv.className = 'ai-card-button-wrapper';
          buttonDiv.innerHTML = `
            <button type="button" class="ai-card-insert-btn" title="Insert into syllabus" aria-label="Insert TLAS">
              <i class="bi bi-download me-2"></i>
              Insert into Syllabus
            </button>
          `;
          
          // Add click handler for insert button
          const insertBtn = buttonDiv.querySelector('.ai-card-insert-btn');
          insertBtn.addEventListener('click', () => {
            insertTlas(tlas);
          });
          
          containerDiv.appendChild(cardDiv);
          containerDiv.appendChild(buttonDiv);
          bubbleDiv.appendChild(containerDiv);
        }
      } else if (isIloResponse(text)) {
        const iloTable = extractIloTable(text);
        
        // Extract intro by removing the markdown table from the text
        let introText = text;
        if (iloTable) {
          // Remove markdown table pattern from text (lines with |)
          const lines = text.split('\n');
          const nonTableLines = [];
          let inTable = false;
          
          for (const line of lines) {
            if (line.trim().startsWith('|')) {
              inTable = true;
            } else if (inTable && line.trim() === '') {
              inTable = false;
            } else if (!inTable) {
              nonTableLines.push(line);
            }
          }
          
          introText = nonTableLines.join('\n').trim();
        }
        
        if (introText) {
          bubbleDiv.innerHTML = formatAIResponse(introText);
        }
        
        // Add a special card for ILOs with insert button
        if (iloTable) {
          const containerDiv = document.createElement('div');
          containerDiv.className = 'ai-ilo-container';
          
          const cardDiv = document.createElement('div');
          cardDiv.className = 'ai-ilo-card';
          cardDiv.innerHTML = `
            <div class="ai-card-header">
              <span class="ai-card-title">Intended Learning Outcomes (ILOs)</span>
            </div>
            <div class="ai-card-body">
              ${iloTable}
            </div>
          `;
          
          const buttonDiv = document.createElement('div');
          buttonDiv.className = 'ai-card-button-wrapper';
          buttonDiv.innerHTML = `
            <button type="button" class="ai-card-insert-btn" title="Insert into syllabus" aria-label="Insert ILOs">
              <i class="bi bi-download me-2"></i>
              Insert into Syllabus
            </button>
          `;
          
          // Add click handler for insert button
          const insertBtn = buttonDiv.querySelector('.ai-card-insert-btn');
          insertBtn.addEventListener('click', () => {
            insertIlos(iloTable);
          });
          
          containerDiv.appendChild(cardDiv);
          containerDiv.appendChild(buttonDiv);
          bubbleDiv.appendChild(containerDiv);
        }
      } else if (isCourseRationalResponse(text)) {
        const rationale = extractCourseRational(text);
        
        // Extract only the conversational intro (everything before the rationale)
        const introText = extractConversationalIntro(text, rationale);
        if (introText) {
          bubbleDiv.innerHTML = formatAIResponse(introText);
        }
        
        // Add a special card for the course rationale with insert button
        if (rationale) {
          const containerDiv = document.createElement('div');
          containerDiv.className = 'ai-course-rationale-container';
          
          const cardDiv = document.createElement('div');
          cardDiv.className = 'ai-course-rationale-card';
          cardDiv.innerHTML = `
            <div class="ai-card-header">
              <span class="ai-card-title">Course Rationale</span>
            </div>
            <div class="ai-card-body">
              <p>${escapeHtml(rationale)}</p>
            </div>
          `;
          
          const buttonDiv = document.createElement('div');
          buttonDiv.className = 'ai-card-button-wrapper';
          buttonDiv.innerHTML = `
            <button type="button" class="ai-card-insert-btn" title="Insert into syllabus" aria-label="Insert course rationale">
              <i class="bi bi-download me-2"></i>
              Insert into Syllabus
            </button>
          `;
          
          // Add click handler for insert button
          const insertBtn = buttonDiv.querySelector('.ai-card-insert-btn');
          insertBtn.addEventListener('click', () => {
            insertCourseRationale(rationale);
          });
          
          containerDiv.appendChild(cardDiv);
          containerDiv.appendChild(buttonDiv);
          bubbleDiv.appendChild(containerDiv);
        }
      } else {
        // Format AI response (support basic markdown-like formatting)
        bubbleDiv.innerHTML = formatAIResponse(text);
      }
    } else {
      bubbleDiv.textContent = text;
    }
    
    msgDiv.appendChild(bubbleDiv);
    messagesContainer.appendChild(msgDiv);
    
    // Save to history (skip loading messages)
    if (!isLoading) {
      conversationHistory.push({ role, text });
    }
    
    // Scroll to bottom
    scrollToBottom();
    
    return msgDiv;
  }

  /**
   * Check if response is about TLA Activities (check FIRST before ILO)
   */
 function isTlaActivitiesResponse(text) {
    const lowerText = text.toLowerCase();
   // Check for TLA-specific indicators
   if (lowerText.includes('tla activities') || 
       lowerText.includes('teaching, learning, and assessment activities')) {
     return text.includes('|');
   }
   // Check for 7-column table structure with specific headers
   if (text.includes('| Ch. |') && 
       text.includes('| Topics / Reading List |') && 
       text.includes('| Wks. |') && 
       text.includes('| Topic Outcomes |') && 
       text.includes('| Delivery Method |')) {
     return true;
   }
   return false;
  }

  /**
   * Check if response is about ILOs (check AFTER TLA Activities)
   */
 function isIloResponse(text) {
    const lowerText = text.toLowerCase();
   // First check it's NOT a TLA Activities response
   if (isTlaActivitiesResponse(text)) {
     return false;
   }
   // Skip assessment tasks tables (rendered plainly)
   if (isAssessmentTasksResponse(text)) {
     return false;
   }
   // Then check for ILO-specific indicators
   return (lowerText.includes('intended learning outcome') || 
           (lowerText.includes('ilo') && !lowerText.includes('tla'))) &&
          text.includes('|'); // Contains table markers
  }

  /**
   * Check if response is about TLAS
   */
  function isTlasResponse(text) {
    const lowerText = text.toLowerCase();
      // Skip if it looks like a course rationale/description
      if (isCourseRationalResponse(text)) return false;
      // Avoid treating table-based TLA Activities as TLAS
      if (isTlaActivitiesResponse(text)) return false;
      // Avoid assessment tasks tables
      if (isAssessmentTasksResponse(text)) return false;

      const hasStrategyCue = lowerText.includes('strategy') || lowerText.includes('strategies') || lowerText.includes('approach');
      const hasModalities = lowerText.includes('hybrid') || lowerText.includes('modality') || lowerText.includes('lecture') || lowerText.includes('laboratory') || lowerText.includes('online');
      const hasAssessmentCue = lowerText.includes('assessment') || lowerText.includes('rubric') || lowerText.includes('exam');

      return (lowerText.includes('teaching') || lowerText.includes('learning') || lowerText.includes('assessment strategies') || hasStrategyCue) &&
        (hasAssessmentCue && hasModalities);
  }

  /**
   * Check if response is about Assessment Tasks (table output)
   */
  function isAssessmentTasksResponse(text) {
    const lowerText = text.toLowerCase();
    const hasAssessmentTaskCue = lowerText.includes('assessment tasks') || lowerText.includes('assessment task') || lowerText.includes('assessment distribution');
    const looksLikeTable = text.includes('|');
    return hasAssessmentTaskCue && looksLikeTable;
  }

  /**
   * Extract ILO table from response
   */
  function extractIloTable(text) {
    // Look for markdown table pattern (lines starting with |)
    const lines = text.split('\n');
    let tableStart = -1;
    let tableEnd = -1;
    
    for (let i = 0; i < lines.length; i++) {
      if (lines[i].trim().startsWith('|')) {
        if (tableStart === -1) {
          tableStart = i;
        }
        tableEnd = i;
      }
    }
    
    if (tableStart !== -1 && tableEnd !== -1) {
      const tableLines = lines.slice(tableStart, tableEnd + 1);
      const tableMarkdown = tableLines.join('\n');
      
      // Convert markdown table to HTML table
      return convertTables(tableMarkdown);
    }
    
    return null;
  }

  /**
   * Convert markdown table to plain text for intro extraction
   */
  function convertTablesToText(htmlTable) {
    // Simple conversion of HTML table back to text for extraction purposes
    return htmlTable.replace(/<[^>]*>/g, '').substring(0, 50) + '...';
  }

  /**
   * Extract TLAS content from response
   */
  function extractTlasContent(text) {
    // Split into paragraphs
    const allParagraphs = text.split('\n\n').map(p => p.trim()).filter(p => p.length > 0);
    
    if (allParagraphs.length === 0) return null;
    
    // Identify and skip the conversational intro
    // Intro paragraphs typically are short or contain phrases like "Here are", "Here's", "I'll provide"
    let startIndex = 0;
    for (let i = 0; i < allParagraphs.length; i++) {
      const para = allParagraphs[i].toLowerCase();
      const isIntro = (
        para.length < 200 || // Intro is usually shorter
        para.includes('here are') ||
        para.includes("here's") ||
        para.includes("i'll provide") ||
        para.includes("i've created") ||
        para.includes("certainly") ||
        para.includes("comprehensive set") ||
        para.includes("tailored for")
      );
      
      if (!isIntro) {
        startIndex = i;
        break;
      }
    }
    
    // Extract the remaining paragraphs (the actual TLAS content)
    const tlasContent = allParagraphs.slice(startIndex);
    
    // Filter for substantial paragraphs (TLAS paragraphs are typically 300+ characters)
    const substantialParagraphs = tlasContent.filter(p => p.length > 250);
    
    if (substantialParagraphs.length > 0) {
      // Return the three main TLAS sections (Assessment Methods, Teaching and Learning, Assessment Instruments)
      return substantialParagraphs.slice(0, 3).join('\n\n').trim();
    }
    
    return null;
  }

  /**
   * Check if response is about course rationale
   */
  function isCourseRationalResponse(text) {
    const lowerText = text.toLowerCase();
    return lowerText.includes('rationale') || lowerText.includes('description') || lowerText.includes('course overview');
  }

  /**
   * Extract the course rationale paragraph from response
   */
  function extractCourseRational(text) {
    // Look for a paragraph enclosed in quotes or a distinct paragraph
    const quoteMatch = text.match(/["'"']([^"'"']{50,}?)["'"']/);
    if (quoteMatch) {
      return quoteMatch[1].trim();
    }
    
    // Try to find the longest single paragraph
    const paragraphs = text.split('\n\n').filter(p => p.trim().length > 50);
    if (paragraphs.length > 0) {
      // Return the last substantial paragraph (usually the description)
      return paragraphs[paragraphs.length - 1].trim();
    }
    
    return null;
  }

  /**
   * Extract only the conversational intro, excluding the rationale
   */
  function extractConversationalIntro(text, rationale) {
    if (!rationale) return text;
    
    // Find the rationale in the text and remove it
    const index = text.indexOf(rationale);
    if (index !== -1) {
      // Get everything before the rationale
      let intro = text.substring(0, index).trim();
      
      // Clean up trailing punctuation/quotes if any
      intro = intro.replace(/["\''…—–-]+\s*$/, '').trim();
      
      return intro || null;
    }
    
    return text;
  }

  /**
   * Insert course rationale into the syllabus form
   */
  function insertCourseRationale(rationale) {
    // Look for the course description textarea/input in the form
    const courseDescInput = document.querySelector('textarea[name*="description"], textarea[data-partial-key="course_info"], input[name*="description"]');
    
    if (courseDescInput) {
      courseDescInput.value = rationale;
      courseDescInput.dispatchEvent(new Event('input', { bubbles: true }));
      courseDescInput.dispatchEvent(new Event('change', { bubbles: true }));
      
      // Show success feedback
      showInsertFeedback('Course rationale inserted successfully!');
    } else {
      showInsertFeedback('Could not find course description field. Please copy manually.', 'warning');
    }
  }

  /**
   * Insert TLAS into the syllabus form
   */
  function insertTlas(tlas) {
    // Look for the TLAS textarea/input in the form
    const tlasInput = document.querySelector('textarea[name*="tla"], textarea[data-partial-key="tlas"], input[name*="tla"]');
    
    if (tlasInput) {
      tlasInput.value = tlas;
      tlasInput.dispatchEvent(new Event('input', { bubbles: true }));
      tlasInput.dispatchEvent(new Event('change', { bubbles: true }));
      
      // Show success feedback
      showInsertFeedback('TLAS inserted successfully!');
    } else {
      showInsertFeedback('Could not find TLAS field. Please copy manually.', 'warning');
    }
  }

  /**
   * Insert ILOs into the syllabus form
   */
  function insertIlos(iloHtml) {
    // Find the ILO sortable list
    const iloList = document.getElementById('syllabus-ilo-sortable');
    
    if (!iloList) {
      showInsertFeedback('Could not find ILO list in syllabus. Please copy manually.', 'warning');
      return;
    }

    try {
      // Parse the HTML table
      const parser = new DOMParser();
      const doc = parser.parseFromString(iloHtml, 'text/html');
      const table = doc.querySelector('table');
      
      if (!table) {
        showInsertFeedback('Could not parse ILO table. Please copy manually.', 'warning');
        return;
      }

      // Extract ILO rows from the table (skip header row)
      const rows = Array.from(table.querySelectorAll('tbody tr'));
      
      if (rows.length === 0) {
        showInsertFeedback('No ILO rows found in table. Please copy manually.', 'warning');
        return;
      }

      // Clear existing ILO rows and placeholders
      iloList.innerHTML = '';

      // Create new rows from the generated ILOs
      rows.forEach((row, index) => {
        const cells = Array.from(row.querySelectorAll('td'));
        if (cells.length < 2) return; // Skip malformed rows

        // Extract ILO text from second cell
        const iloText = cells[1].textContent.trim();
        const iloCode = `ILO${index + 1}`;

        // Create new row matching the partial's structure
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-id', `new-${Date.now()}-${index}`);
        newRow.innerHTML = `
          <td class="text-center align-middle">
            <div class="ilo-badge fw-semibold">${iloCode}</div>
          </td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
                <i class="bi bi-grip-vertical"></i>
              </span>
              <textarea
                name="ilos[]"
                class="cis-textarea cis-field autosize flex-grow-1"
                placeholder="-"
                rows="1"
                style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                required>${iloText}</textarea>
              <input type="hidden" name="code[]" value="${iloCode}">
              <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ilo ms-2" title="Delete ILO">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        `;
        
        iloList.appendChild(newRow);
      });

      // Trigger initialization of the ILO list (autosize, renumbering, etc.)
      if (window.initAutosize) {
        try { window.initAutosize(); } catch (e) { /* noop */ }
      }

      // Mark as unsaved
      if (window.updateUnsavedCount) {
        try { window.updateUnsavedCount(); } catch (e) { /* noop */ }
      }

      // Dispatch event if syllabus-ilo.js is listening
      try {
        document.dispatchEvent(new CustomEvent('ilo:changed', { 
          detail: { 
            count: rows.length,
            action: 'insert_from_ai'
          } 
        }));
      } catch (e) { /* noop */ }

      showInsertFeedback(`Successfully inserted ${rows.length} ILO(s)!`);
    } catch (error) {
      console.error('Error inserting ILOs:', error);
      showInsertFeedback('Error inserting ILOs. Please copy manually.', 'warning');
    }
  }

  /**
   * Insert TLA Activities into the syllabus form
   */
  function insertTlaActivities(tlaHtml) {
    // Find the TLA table body
    const tlaBody = document.querySelector('#tlaTable tbody');
    
    if (!tlaBody) {
      showInsertFeedback('Could not find TLA Activities table in syllabus. Please copy manually.', 'warning');
      return;
    }

    try {
      // Parse the HTML table
      const parser = new DOMParser();
      const doc = parser.parseFromString(tlaHtml, 'text/html');
      const table = doc.querySelector('table');
      
      if (!table) {
        showInsertFeedback('Could not parse TLA table. Please copy manually.', 'warning');
        return;
      }

      // Extract TLA rows from the table (skip header row)
      const rows = Array.from(table.querySelectorAll('tbody tr'));
      
      if (rows.length === 0) {
        showInsertFeedback('No TLA activity rows found in table. Please copy manually.', 'warning');
        return;
      }

      // Remove placeholder if exists
      const placeholder = tlaBody.querySelector('#tla-placeholder');
      if (placeholder) {
        placeholder.remove();
      }

      // Clear existing TLA rows
      tlaBody.innerHTML = '';

      // Create new rows from the generated TLA activities
      rows.forEach((row, index) => {
        const cells = Array.from(row.querySelectorAll('td'));
        if (cells.length < 7) return; // Skip malformed rows (need 7 columns)

        // Extract data from each cell
        const ch = cells[0].textContent.trim();
        // Preserve newlines and blank lines in topic cell (for main topic + tasks formatting)
        const topic = cells[1].textContent.split('\n').map(line => line.trim()).join('\n');
        const wks = cells[2].textContent.trim();
        const outcomes = cells[3].textContent.trim();
        const ilo = cells[4].textContent.trim();
        const so = cells[5].textContent.trim();
        const delivery = cells[6].textContent.trim();

        // Create new row matching the partial's structure
        const newRow = document.createElement('tr');
        newRow.className = 'text-center align-middle';
        newRow.setAttribute('data-tla-id', '');
        newRow.innerHTML = `
          <td class="tla-ch">
            <input name="tla[${index}][ch]" form="syllabusForm" class="form-control cis-input text-center" value="${ch}" placeholder="-">
          </td>
          <td class="tla-topic text-start">
            <textarea name="tla[${index}][topic]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="-">${topic}</textarea>
          </td>
          <td class="tla-wks">
            <input name="tla[${index}][wks]" form="syllabusForm" class="form-control cis-input text-center" value="${wks}" placeholder="-">
          </td>
          <td class="tla-outcomes text-start">
            <textarea name="tla[${index}][outcomes]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="-">${outcomes}</textarea>
          </td>
          <td class="tla-ilo">
            <input name="tla[${index}][ilo]" form="syllabusForm" class="form-control cis-input text-center" value="${ilo}" placeholder="-">
          </td>
          <td class="tla-so">
            <input name="tla[${index}][so]" form="syllabusForm" class="form-control cis-input text-center" value="${so}" placeholder="-">
          </td>
          <td class="tla-delivery">
            <textarea name="tla[${index}][delivery]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="1" placeholder="-">${delivery}</textarea>
          </td>
          <td class="tla-actions text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-tla-row" data-id="" title="Delete Row">
              <i class="bi bi-trash"></i>
            </button>
          </td>
          <input type="hidden" class="tla-id-field" name="tla[${index}][id]" value="">
          <input type="hidden" class="tla-position-field" name="tla[${index}][position]" value="${index}">
        `;
        
        tlaBody.appendChild(newRow);
      });

      // Trigger autosize initialization if available
      if (window.initAutosize) {
        try { window.initAutosize(); } catch (e) { /* noop */ }
      }

      // Trigger realtime context rebuild if available
      if (typeof window.rebuildTlaRealtimeContext === 'function') {
        try { window.rebuildTlaRealtimeContext(); } catch (e) { /* noop */ }
      }

      // Mark as unsaved
      if (window.updateUnsavedCount) {
        try { window.updateUnsavedCount(); } catch (e) { /* noop */ }
      }

      showInsertFeedback(`Successfully inserted ${rows.length} TLA activity row(s)!`);
    } catch (error) {
      console.error('Error inserting TLA activities:', error);
      showInsertFeedback('Error inserting TLA activities. Please copy manually.', 'warning');
    }
  }

  /**
   * Show feedback for insert action
   */
  function showInsertFeedback(message, type = 'success') {
    const feedback = document.createElement('div');
    feedback.className = `ai-insert-feedback ai-insert-feedback-${type}`;
    feedback.textContent = message;
    feedback.style.cssText = `
      position: fixed;
      bottom: 2rem;
      left: 50%;
      transform: translateX(-50%);
      padding: 0.75rem 1.5rem;
      background: ${type === 'success' ? '#10b981' : '#f59e0b'};
      color: white;
      border-radius: 8px;
      font-size: 0.9rem;
      z-index: 2000;
      animation: slideUp 0.3s ease;
    `;
    document.body.appendChild(feedback);
    
    setTimeout(() => {
      feedback.style.animation = 'slideDown 0.3s ease';
      setTimeout(() => feedback.remove(), 300);
    }, 3000);
  }

  /**
   * Escape HTML entities
   */
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  /**
   * Format AI response with basic markdown support
   */
  function formatAIResponse(text) {
    let html = text;

    // Escape HTML
    html = html
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');

    // Code blocks
    html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

    // Inline code
    html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

    // Bold
    html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

    // Italic
    html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');

    // Markdown tables -> HTML tables
    html = convertTables(html);

    // Line breaks to paragraphs (preserve tables)
    html = html.split('\n\n').map(para => {
      const trimmed = para.trim();
      if (!trimmed) return '';

      if (trimmed.includes('<table')) {
        return trimmed;
      }

      // Lists
      if (/^[-*]\s/.test(trimmed)) {
        const items = trimmed.split('\n').map(line => {
          return line.replace(/^[-*]\s/, '<li>') + '</li>';
        }).join('');
        return '<ul>' + items + '</ul>';
      }

      return '<p>' + trimmed.replace(/\n/g, '<br>') + '</p>';
    }).join('');

    return html;
  }

  // Convert simple markdown tables to HTML tables
  function convertTables(text) {
    const lines = text.split('\n');
    const out = [];
    let i = 0;

    while (i < lines.length) {
      const line = lines[i];
      const isRow = /^\s*\|.+\|\s*$/.test(line);
      const hasNext = i + 1 < lines.length;
      const isDivider = hasNext && /^\s*\|?\s*:?-{3,}.*\|\s*$/.test(lines[i + 1]);

      if (isRow && isDivider) {
        const headerCells = line.trim().replace(/^\||\|$/g, '').split('|').map(c => c.trim());
        i += 2; // skip header and divider
        const bodyRows = [];
        while (i < lines.length && /^\s*\|.+\|\s*$/.test(lines[i])) {
          bodyRows.push(lines[i].trim().replace(/^\||\|$/g, ''));
          i++;
        }

        let tableHtml = '<div class="ai-chat-table-wrap"><table class="ai-chat-table"><thead><tr>';
        headerCells.forEach(cell => { tableHtml += '<th>' + cell + '</th>'; });
        tableHtml += '</tr></thead><tbody>';

        if (bodyRows.length === 0) {
          tableHtml += '<tr><td colspan="' + headerCells.length + '">-</td></tr>';
        } else {
          bodyRows.forEach(row => {
            const cells = row.split('|').map(c => c.trim());
            tableHtml += '<tr>' + cells.map(c => '<td>' + c + '</td>').join('') + '</tr>';
          });
        }

        tableHtml += '</tbody></table></div>';
        out.push(tableHtml);
        continue;
      }

      out.push(line);
      i++;
    }

    return out.join('\n');
  }

  /**
   * Scroll messages container to bottom
   */
  function scrollToBottom() {
    if (!messagesContainer) return;
    
    requestAnimationFrame(() => {
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
    });
  }

  /**
   * Backend is handled by SVAI in ai.js; UI-only below
   */

  /**
   * Public API
   */
  window.AIChat = {
    open: openPanel,
    close: closePanel,
    send: (message) => {
      if (input) {
        input.value = message;
        handleSend();
      }
    }
  };

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
