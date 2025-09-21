(function($){
  if(typeof skybugProgramsL10n==='undefined') return;
  // List filtering
  const $search = $('#skybug-program-search');
  const $typeFilter = $('#skybug-program-type-filter');
  
  function filterPrograms() {
    const searchQuery = $search.val().toLowerCase();
    const typeFilter = $typeFilter.val();
    
    $('.skybug-program-card').each(function(){
      const $card = $(this);
      const searchMatch = !searchQuery || ($card.data('search') || '').toString().indexOf(searchQuery) !== -1;
      const typeMatch = !typeFilter || $card.data('type') === typeFilter;
      
      $card.toggle(searchMatch && typeMatch);
    });
  }
  
  $search.on('input', filterPrograms);
  $typeFilter.on('change', filterPrograms);
  // Reveal key
  $(document).on('click','[data-action="reveal-key"]', function(){
    const card = $(this).closest('.skybug-program-card');
    const code = card.find('.skybug-masked-key');
    const full = code.data('full');
    if(!full) return;
    const revealed = !!code.data('revealed');
    if(revealed){
      code.text(full.substring(0,4)+'\u2022\u2022\u2022\u2022'+full.substring(full.length-4)).data('revealed',false);
      $(this).text('🔐 '+skybugProgramsL10n.showKey);
    } else {
      code.text(full).data('revealed',true);
      $(this).text('🙈 '+skybugProgramsL10n.hideKey);
    }
  });
  // Regenerate key
  $(document).on('click','[data-action="regenerate-key"]', function(){
    if(!confirm(skybugProgramsL10n.confirmRegen)) return;
    const btn = $(this); const card = btn.closest('.skybug-program-card'); const pid = btn.data('program-id');
    btn.prop('disabled',true).text('…');
    $.post(ajaxurl,{action:'skybug_regenerate_program_key',nonce:skybugProgramsL10n.nonce,program_id:pid}, function(r){
      if(r && r.success){
        const code = card.find('.skybug-masked-key');
        code.text(r.data.masked).data('full', r.data.key).data('revealed', false);
        card.find('.skybug-program-feedback').css('color','#155724').text(skybugProgramsL10n.regenOk);
      } else {
        card.find('.skybug-program-feedback').css('color','#721c24').text(skybugProgramsL10n.regenFail);
      }
      btn.prop('disabled',false).text('♻️ '+skybugProgramsL10n.regenerate);
    });
  });
  // Test webhook
  $(document).on('click','[data-action="test-webhook"]', function(){
    const btn=$(this); const card=btn.closest('.skybug-program-card'); const pid=btn.data('program-id');
    btn.prop('disabled',true).text('…');
    $.post(ajaxurl,{action:'skybug_test_program_webhook',nonce:skybugProgramsL10n.nonce,program_id:pid}, function(r){
      if(r && r.success){
        card.find('.skybug-program-feedback').css('color','#155724').text(skybugProgramsL10n.testSent);
      } else {
        card.find('.skybug-program-feedback').css('color','#721c24').text(skybugProgramsL10n.testFail);
      }
      btn.prop('disabled',false).text('📡 '+skybugProgramsL10n.testBtn);
    });
  });
  
  // Check repository commits
  $(document).on('click','[data-action="check-repo"]', function(){
    const btn = $(this);
    const card = btn.closest('.skybug-program-card');
    const programId = btn.data('program-id');
    
    btn.prop('disabled', true).text('⏳...');
    
    $.post(ajaxurl, {
      action: 'skybug_check_repo_commits',
      nonce: skybugProgramsL10n.nonce,
      program_id: programId
    }, function(response) {
      if (response && response.success) {
        showCommitsModal(response.data);
        card.find('.skybug-program-feedback').css('color','#155724').text('Commits hentet');
      } else {
        card.find('.skybug-program-feedback').css('color','#721c24').text(response.data || 'Feil ved henting av commits');
      }
    }).fail(function() {
      card.find('.skybug-program-feedback').css('color','#721c24').text('Network error');
    }).always(function() {
      btn.prop('disabled', false).text('📝 ' + skybugProgramsL10n.commits);
    });
  });
  
  function showCommitsModal(commits) {
    const modal = $('<div class="skybug-commits-modal" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:10000;display:flex;align-items:center;justify-content:center">');
    
    let content = '<div style="background:white;padding:20px;border-radius:8px;max-width:600px;max-height:80vh;overflow-y:auto;">';
    content += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">';
    content += '<h3 style="margin:0">📝 Latest Commits</h3>';
    content += '<button class="skybug-close-modal" style="background:none;border:none;font-size:18px;cursor:pointer">×</button>';
    content += '</div>';
    
    if (commits && commits.length > 0) {
      commits.forEach(commit => {
        content += '<div style="border-bottom:1px solid #eee;padding:12px 0;">';
        content += '<div style="font-weight:600;margin-bottom:4px;"><a href="' + commit.url + '" target="_blank" style="text-decoration:none;color:#0073aa">' + commit.sha + '</a></div>';
        content += '<div style="color:#333;margin-bottom:4px;">' + commit.message.split('\n')[0] + '</div>';
        content += '<div style="font-size:12px;color:#666;">' + commit.author + ' • ' + commit.date + '</div>';
        content += '</div>';
      });
    } else {
      content += '<p style="color:#666;text-align:center;">No commits found</p>';
    }
    
    content += '</div>';
    
    modal.html(content);
    
    modal.on('click', function(e) {
      if (e.target === this || $(e.target).hasClass('skybug-close-modal')) {
        modal.remove();
      }
    });
    
    $('body').append(modal);
  }
  
  // API Tester toggle
  $(document).on('click', '.skybug-api-tester-toggle', function(){
    const btn = $(this);
    const programId = btn.data('program-id');
    const panel = $('#api-tester-' + programId);
    
    if (panel.is(':visible')) {
      panel.slideUp(200);
      btn.text('🔌 ' + skybugProgramsL10n.apiTest);
    } else {
      panel.slideDown(200);
      btn.text('🔌 ' + skybugProgramsL10n.hideApiTest);
    }
  });
  
  // API endpoint testing
  $(document).on('click', '.skybug-test-api', function(){
    const btn = $(this);
    const programId = btn.data('program-id');
    const panel = $('#api-tester-' + programId);
    const method = panel.find('.api-method-select').val();
    const endpoint = panel.find('.api-endpoint-input').val();
    
    if (!endpoint.trim()) {
      alert('Vennligst skriv inn et endpoint');
      return;
    }
    
    const responseContainer = panel.find('.api-response-container');
    responseContainer.html('<div style="color:#007cba">⏳ Testing endpoint...</div>');
    
    btn.prop('disabled', true).text('Testing...');
    
    $.post(ajaxurl, {
      action: 'skybug_test_api_endpoint',
      nonce: skybugProgramsL10n.nonce,
      program_id: programId,
      method: method,
      endpoint: endpoint
    }, function(response) {
      if (response && response.success && response.data.success) {
        const data = response.data;
        let html = '<div style="margin-bottom:8px;">';
        html += '<span style="color:#28a745;font-weight:bold">' + data.status_code + '</span> ';
        html += '<span style="color:#6c757d">• ' + data.response_time + 'ms • ' + data.url + '</span>';
        html += '</div>';
        html += '<pre style="margin:0;white-space:pre-wrap;word-wrap:break-word;background:#f8f9fa;padding:6px;border-radius:3px;border:1px solid #e9ecef">';
        html += data.body ? data.body.substring(0, 500) + (data.body.length > 500 ? '...' : '') : '(Empty response)';
        html += '</pre>';
        responseContainer.html(html);
      } else if (response && response.success && !response.data.success) {
        const data = response.data;
        let html = '<div style="margin-bottom:8px;">';
        html += '<span style="color:#dc3545;font-weight:bold">ERROR</span> ';
        html += '<span style="color:#6c757d">• ' + data.response_time + 'ms • ' + data.url + '</span>';
        html += '</div>';
        html += '<div style="color:#dc3545">' + data.error + '</div>';
        responseContainer.html(html);
      } else {
        responseContainer.html('<div style="color:#dc3545">Failed to test endpoint</div>');
      }
    }).fail(function() {
      responseContainer.html('<div style="color:#dc3545">Network error</div>');
    }).always(function() {
      btn.prop('disabled', false).text('Test');
    });
  });
  
  // Metrics toggle and loading
  $(document).on('click', '.skybug-metrics-toggle', function(){
    const btn = $(this);
    const programId = btn.data('program-id');
    const panel = $('#metrics-' + programId);
    
    if (panel.is(':visible')) {
      panel.slideUp(200);
      btn.text('📊 ' + skybugProgramsL10n.metrics);
      return;
    }
    
    panel.slideDown(200);
    btn.text('📊 ' + skybugProgramsL10n.hideMetrics);
    
    // Load metrics if not already loaded
    if (!panel.data('loaded')) {
      loadProgramMetrics(programId);
      panel.data('loaded', true);
    }
  });
  
  function loadProgramMetrics(programId) {
    const panel = $('#metrics-' + programId);
    const loading = panel.find('.skybug-metrics-loading');
    const content = panel.find('.skybug-metrics-content');
    
    loading.show();
    content.hide();
    
    $.post(ajaxurl, {
      action: 'skybug_get_program_metrics',
      nonce: skybugProgramsL10n.nonce,
      program_id: programId
    }, function(response) {
      loading.hide();
      
      if (response && response.success) {
        const data = response.data;
        
        // Update metric cards
        panel.find('[data-metric="resolution_rate"]').text(data.resolution_rate + '%');
        panel.find('[data-metric="avg_resolution_time"]').text(data.avg_resolution_time);
        panel.find('[data-metric="health_score"]').text(data.health_score);
        
        // Update health score color
        const healthElement = panel.find('[data-metric="health_score"]');
        if (data.health_score >= 80) {
          healthElement.css('color', '#28a745');
        } else if (data.health_score >= 60) {
          healthElement.css('color', '#ffc107');
        } else {
          healthElement.css('color', '#dc3545');
        }
        
        // Draw trend chart
        drawTrendChart(programId, data.bug_rate_trend);
        
        content.show();
      } else {
        loading.html('❌ ' + (response.data || 'Failed to load metrics'));
      }
    }).fail(function() {
      loading.html('❌ Network error');
    });
  }
  
  function drawTrendChart(programId, trendData) {
    const canvas = document.querySelector('[data-program-id="' + programId + '"]');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    
    if (!trendData || trendData.length === 0) {
      ctx.fillStyle = '#6c757d';
      ctx.font = '12px sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText('No data', width/2, height/2);
      return;
    }
    
    const maxValue = Math.max(...trendData.map(d => d.count), 1);
    const stepWidth = width / (trendData.length - 1 || 1);
    
    // Draw trend line
    ctx.strokeStyle = '#2196f3';
    ctx.lineWidth = 2;
    ctx.beginPath();
    
    trendData.forEach((point, index) => {
      const x = index * stepWidth;
      const y = height - (point.count / maxValue) * (height - 10);
      
      if (index === 0) {
        ctx.moveTo(x, y);
      } else {
        ctx.lineTo(x, y);
      }
    });
    
    ctx.stroke();
    
    // Draw points
    ctx.fillStyle = '#2196f3';
    trendData.forEach((point, index) => {
      const x = index * stepWidth;
      const y = height - (point.count / maxValue) * (height - 10);
      
      ctx.beginPath();
      ctx.arc(x, y, 3, 0, 2 * Math.PI);
      ctx.fill();
    });
  }
  
  // Image upload functionality
  $(document).on('click', '.skybug-upload-image', function() {
    const programId = $(this).data('program-id');
    $('#program-image-' + programId).click();
  });
  
  $(document).on('change', 'input[type="file"][data-program-id]', function() {
    const fileInput = this;
    const programId = $(fileInput).data('program-id');
    const card = $(fileInput).closest('.skybug-program-card');
    
    if (!fileInput.files || !fileInput.files[0]) return;
    
    const formData = new FormData();
    formData.append('action', 'skybug_upload_program_image');
    formData.append('nonce', skybugProgramsL10n.nonce);
    formData.append('program_id', programId);
    formData.append('image', fileInput.files[0]);
    
    const uploadBtn = card.find('.skybug-upload-image');
    const originalText = uploadBtn.text();
    uploadBtn.prop('disabled', true).text('⏳ Laster opp...');
    
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        if (response && response.success) {
          // Update image display
          const imageContainer = card.find('.skybug-program-image-container');
          imageContainer.html('<img src="' + response.data.thumbnail + '" alt="Program image" style="width:100%;height:100%;object-fit:cover" />');
          
          // Update buttons
          uploadBtn.text('🖼️ ' + skybugProgramsL10n.changeImage);
          
          // Add remove button if not exists
          if (!card.find('.skybug-remove-image').length) {
            uploadBtn.after('<button type="button" class="button button-small skybug-remove-image" data-program-id="' + programId + '" style="color:#dc3545">❌ ' + skybugProgramsL10n.removeImage + '</button>');
          }
          
          card.find('.skybug-program-feedback').css('color', '#155724').text(response.data.message);
        } else {
          card.find('.skybug-program-feedback').css('color', '#721c24').text(response.data || 'Upload failed');
        }
      },
      error: function() {
        card.find('.skybug-program-feedback').css('color', '#721c24').text('Network error');
      },
      complete: function() {
        uploadBtn.prop('disabled', false);
        fileInput.value = ''; // Reset file input
      }
    });
  });
  
  // Image removal
  $(document).on('click', '.skybug-remove-image', function() {
    if (!confirm(skybugProgramsL10n.confirmRemoveImage)) return;
    
    const programId = $(this).data('program-id');
    const card = $(this).closest('.skybug-program-card');
    const removeBtn = $(this);
    
    removeBtn.prop('disabled', true).text('⏳...');
    
    $.post(ajaxurl, {
      action: 'skybug_remove_program_image',
      nonce: skybugProgramsL10n.nonce,
      program_id: programId
    }, function(response) {
      if (response && response.success) {
        // Update image display to default
        const imageContainer = card.find('.skybug-program-image-container');
        imageContainer.html('<span style="font-size:24px;opacity:0.5">📦</span>');
        
        // Update upload button text
        card.find('.skybug-upload-image').text('🖼️ ' + skybugProgramsL10n.uploadImage);
        
        // Remove the remove button
        removeBtn.remove();
        
        card.find('.skybug-program-feedback').css('color', '#155724').text(response.data.message);
      } else {
        card.find('.skybug-program-feedback').css('color', '#721c24').text(response.data || 'Remove failed');
        removeBtn.prop('disabled', false).text('❌ ' + skybugProgramsL10n.removeImage);
      }
    }).fail(function() {
      card.find('.skybug-program-feedback').css('color', '#721c24').text('Network error');
      removeBtn.prop('disabled', false).text('❌ ' + skybugProgramsL10n.removeImage);
    });
  });
  
  // Template functionality
  $('#skybug-template-btn').on('click', function(e) {
    e.preventDefault();
    $('.skybug-template-menu').toggle();
  });
  
  // Close template menu when clicking outside
  $(document).on('click', function(e) {
    if (!$(e.target).closest('.button-group').length) {
      $('.skybug-template-menu').hide();
    }
  });
  
  // Template selection
  $(document).on('click', '.skybug-template-option', function(e) {
    e.preventDefault();
    
    const template = $(this).data('template');
    const templateName = $(this).find('div:first').text();
    
    const programName = prompt('Navn på nytt program:', templateName + ' Project');
    if (!programName) return;
    
    const btn = $('#skybug-template-btn');
    const originalText = btn.text();
    btn.prop('disabled', true).text('⏳ Oppretter...');
    
    $.post(ajaxurl, {
      action: 'skybug_create_program_from_template',
      nonce: skybugProgramsL10n.nonce,
      template: template,
      program_name: programName
    }, function(response) {
      if (response && response.success) {
        // Redirect to edit page
        window.location.href = response.data.edit_url;
      } else {
        alert('Feil: ' + (response.data || 'Unknown error'));
      }
    }).fail(function() {
      alert('Network error');
    }).always(function() {
      btn.prop('disabled', false).text(originalText);
      $('.skybug-template-menu').hide();
    });
  });
  
  // Real-time updates with polling fallback (since WebSocket requires server setup)
  let updateInterval;
  
  function startRealTimeUpdates() {
    // Poll for updates every 30 seconds
    updateInterval = setInterval(function() {
      refreshProgramStats();
    }, 30000);
  }
  
  function stopRealTimeUpdates() {
    if (updateInterval) {
      clearInterval(updateInterval);
    }
  }
  
  function refreshProgramStats() {
    $('.skybug-program-card').each(function() {
      const $card = $(this);
      const programId = $card.find('[data-program-id]').first().data('program-id');
      
      if (!programId) return;
      
      // Only update if metrics panel is visible
      const metricsPanel = $card.find('#metrics-' + programId);
      if (metricsPanel.is(':visible') && metricsPanel.data('loaded')) {
        loadProgramMetrics(programId);
      }
    });
  }
  
  // Start real-time updates when page loads
  $(document).ready(function() {
    startRealTimeUpdates();
    
    // Add touch support for mobile
    if ('ontouchstart' in window) {
      addTouchSupport();
    }
  });
  
  // Stop updates when page is hidden
  document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
      stopRealTimeUpdates();
    } else {
      startRealTimeUpdates();
    }
  });
  
  // Touch gesture support for mobile
  function addTouchSupport() {
    let touchStartX, touchStartY;
    
    $(document).on('touchstart', '.skybug-program-card', function(e) {
      touchStartX = e.originalEvent.touches[0].clientX;
      touchStartY = e.originalEvent.touches[0].clientY;
    });
    
    $(document).on('touchend', '.skybug-program-card', function(e) {
      if (!touchStartX || !touchStartY) return;
      
      const touchEndX = e.originalEvent.changedTouches[0].clientX;
      const touchEndY = e.originalEvent.changedTouches[0].clientY;
      
      const deltaX = touchEndX - touchStartX;
      const deltaY = touchEndY - touchStartY;
      
      // Swipe right to reveal metrics
      if (deltaX > 50 && Math.abs(deltaY) < 100) {
        const metricsToggle = $(this).find('.skybug-metrics-toggle');
        if (metricsToggle.length) {
          metricsToggle.click();
        }
      }
      
      // Swipe left to reveal API tester
      if (deltaX < -50 && Math.abs(deltaY) < 100) {
        const apiToggle = $(this).find('.skybug-api-tester-toggle');
        if (apiToggle.length) {
          apiToggle.click();
        }
      }
      
      touchStartX = null;
      touchStartY = null;
    });
    
    // Long press for quick actions
    let longPressTimer;
    
    $(document).on('touchstart', '.skybug-program-card h2 a', function(e) {
      const $link = $(this);
      longPressTimer = setTimeout(function() {
        // Show quick actions menu on long press
        showQuickActions($link.closest('.skybug-program-card'));
      }, 500);
    });
    
    $(document).on('touchend touchcancel', '.skybug-program-card h2 a', function() {
      clearTimeout(longPressTimer);
    });
  }
  
  function showQuickActions($card) {
    const programId = $card.find('[data-program-id]').first().data('program-id');
    
    const actions = $('<div class="skybug-quick-actions" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;border:1px solid #ddd;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.2);z-index:10001;min-width:200px">');
    
    let content = '<div style="padding:12px;border-bottom:1px solid #eee;font-weight:600;text-align:center">Quick Actions</div>';
    content += '<a href="#" class="quick-action" data-action="metrics" style="display:block;padding:10px 16px;text-decoration:none;color:#333;border-bottom:1px solid #f0f0f0">📊 Show Metrics</a>';
    content += '<a href="#" class="quick-action" data-action="api-test" style="display:block;padding:10px 16px;text-decoration:none;color:#333;border-bottom:1px solid #f0f0f0">🔌 API Test</a>';
    content += '<a href="#" class="quick-action" data-action="edit" style="display:block;padding:10px 16px;text-decoration:none;color:#333;border-bottom:1px solid #f0f0f0">✏️ Edit Program</a>';
    content += '<a href="#" class="quick-action-close" style="display:block;padding:10px 16px;text-decoration:none;color:#dc3545;text-align:center">✕ Close</a>';
    
    actions.html(content);
    
    actions.on('click', '.quick-action', function(e) {
      e.preventDefault();
      const action = $(this).data('action');
      
      if (action === 'metrics') {
        $card.find('.skybug-metrics-toggle').click();
      } else if (action === 'api-test') {
        $card.find('.skybug-api-tester-toggle').click();
      } else if (action === 'edit') {
        window.location.href = $card.find('h2 a').attr('href');
      }
      
      actions.remove();
    });
    
    actions.on('click', '.quick-action-close, ', function(e) {
      if (e.target === this || $(e.target).hasClass('quick-action-close')) {
        actions.remove();
      }
    });
    
    $('body').append(actions);
  }
  
})(jQuery);
