(function(){
    console.log('SKYBUG DEBUG: bugs-admin.js starting initialization');
    function qs(sel,ctx){return (ctx||document).querySelector(sel);} 
    function qsa(sel,ctx){return Array.prototype.slice.call((ctx||document).querySelectorAll(sel));}
    const grid = qs('#skybug-bug-grid');
    console.log('SKYBUG DEBUG: grid element found =', !!grid);
    if(!grid) {
        console.log('SKYBUG DEBUG: No grid found, exiting bugs-admin.js');
        return;
    }
    const chips = qsa('.skybug-chip');
    const searchInput = qs('#skybug-search');
    const prioritySelect = qs('#skybug-priority-filter');

    function applyFilters(){
        const activeChip = chips.find(c=>c.classList.contains('active'));
        const statusFilter = activeChip ? activeChip.getAttribute('data-filter-status') : 'all';
        const term = (searchInput?.value || '').trim().toLowerCase();
        const priority = prioritySelect?.value || '';
        let visible = 0;
        qsa('.skybug-bug-card', grid).forEach(card=>{
            const s = card.getAttribute('data-status');
            const p = card.getAttribute('data-priority');
            const searchData = card.getAttribute('data-search');
            let ok = true;
            if(statusFilter !== 'all' && s !== statusFilter) ok = false;
            if(ok && priority && p !== priority) ok = false;
            if(ok && term && searchData.indexOf(term) === -1) ok = false;
            card.style.display = ok ? '' : 'none';
            if(ok) visible++;
        });
        grid.classList.toggle('empty', visible === 0);
        if(visible === 0){ grid.setAttribute('data-empty-label', window.skybugEmptyLabel || 'Ingen saker matcher filter'); }
        const countEl = document.querySelector('.skybug-title-count');
        if(countEl){ countEl.textContent = '('+visible+')'; }
    }

    chips.forEach(chip=>{
        chip.addEventListener('click', ()=>{
            chips.forEach(c=>c.classList.remove('active'));
            chip.classList.add('active');
            applyFilters();
        });
    });
    // Sett første chip aktiv (Alle)
    const first = chips[0]; if(first) first.classList.add('active');

    searchInput && searchInput.addEventListener('input', applyFilters);
    prioritySelect && prioritySelect.addEventListener('change', applyFilters);

    applyFilters();

    // Mass delete handling
    const deleteBtn = qs('#skybug-delete-selected');
    function updateDeleteState(){
        const anyChecked = qsa('.skybug-select-bug:checked').length>0;
        if(deleteBtn){ deleteBtn.disabled = !anyChecked; }
    }
    document.addEventListener('change', function(e){
        if(e.target.classList && e.target.classList.contains('skybug-select-bug')){
            updateDeleteState();
        }
    });
    deleteBtn && deleteBtn.addEventListener('click', function(){
        const ids = qsa('.skybug-select-bug:checked').map(c=>c.value);
        if(!ids.length) return;
    const confirmMsg = (window.skybugBugs && skybugBugs.i18n && skybugBugs.i18n.deleteConfirm) ? skybugBugs.i18n.deleteConfirm : ('Slette '+ids.length+' saker?');
    if(!confirm(confirmMsg)) return;
        // Simple sequential requests (placeholder) – ideally one custom AJAX endpoint
        let done=0, failed=0;
        ids.forEach(id=>{
            const formData = new FormData();
            formData.append('action','delete-post');
            formData.append('post', id);
            formData.append('_wpnonce', (skybugBugs?.nonces?.bulkDelete||''));
            fetch(ajaxurl, {method:'POST', body:formData}).then(r=>r.text()).then(()=>{
                const card = document.querySelector('.skybug-bug-card input.skybug-select-bug[value="'+id+'"]')?.closest('.skybug-bug-card');
                if(card){ card.remove(); }
                done++; updateDeleteState(); applyFilters();
            }).catch(()=>{failed++;});
        });
    });

    // IMAP fetch trigger (AJAX placeholder)
    const fetchBtn = qs('#skybug-fetch-imap');
    fetchBtn && fetchBtn.addEventListener('click', function(){
        if(fetchBtn.disabled) return;
    fetchBtn.disabled = true; const orig = fetchBtn.textContent; fetchBtn.textContent = (skybugBugs?.i18n?.imapFetching)||'Henter...';
        const fd = new FormData();
        fd.append('action','skybug_check_imap_emails');
    fd.append('nonce', (skybugBugs?.nonces?.imap||''));
        fetch(ajaxurl,{method:'POST',body:fd}).then(r=>r.json()).then(data=>{
            fetchBtn.textContent = orig;
            fetchBtn.disabled = false;
            if(data && data.success){
                // Optionally reload to show new issues
                location.reload();
            } else {
                alert((skybugBugs?.i18n?.imapError)||'IMAP fetch feilet');
            }
    }).catch(()=>{fetchBtn.textContent=orig;fetchBtn.disabled=false;alert((skybugBugs?.i18n?.imapError)||'IMAP fetch feilet');});
    });

    // Recent emails box logic
    const recentList = qs('#skybug-recent-emails-list');
    const refreshBtn = qs('#skybug-refresh-recent-emails');
    console.log('SKYBUG DEBUG: Recent emails elements - list =', !!recentList, 'refreshBtn =', !!refreshBtn);
    console.log('SKYBUG DEBUG: skybugBugs object =', typeof window.skybugBugs, window.skybugBugs);
    console.log('SKYBUG DEBUG: ajaxurl =', typeof window.ajaxurl, window.ajaxurl);
    function loadRecentEmails(){
        console.log('SKYBUG DEBUG: loadRecentEmails() called');
        if(!recentList) {
            console.log('SKYBUG DEBUG: No recentList element, returning');
            return;
        }
        console.log('SKYBUG DEBUG: Setting loading state');
        recentList.innerHTML = '<li style="color:#6c757d;font-style:italic">'+(skybugBugs?.i18n?.imapFetching||'Henter...')+'</li>';
        if(refreshBtn){ 
            refreshBtn.disabled = true; 
            console.log('SKYBUG DEBUG: Disabled refresh button');
        }
        const fd = new FormData();
        fd.append('action','skybug_fetch_recent_imap_emails');
        fd.append('nonce', (skybugBugs?.nonces?.recentEmails||''));
        console.log('SKYBUG DEBUG: FormData prepared - action=skybug_fetch_recent_imap_emails, nonce=', (skybugBugs?.nonces?.recentEmails||'MISSING'));
        console.log('SKYBUG DEBUG: Making fetch request to', window.ajaxurl);
        fetch(ajaxurl,{method:'POST',body:fd}).then(r=>{
            console.log('SKYBUG DEBUG: Fetch response received, status=', r.status, 'ok=', r.ok);
            return r.text();
        }).then(txt=>{
            console.log('SKYBUG DEBUG: Raw response text length=', txt.length, 'first 200 chars=', txt.substring(0, 200));
            let data; try { 
                data = JSON.parse(txt); 
                console.log('SKYBUG DEBUG: JSON parsed successfully');
            } catch(e){
                console.log('SKYBUG DEBUG: JSON parse failed, error=', e.message);
                recentList.innerHTML = '<li style="color:#dc3545">Parse feil ('+escapeHtml(e.message)+')</li>';
                if(refreshBtn){ refreshBtn.disabled=false; }
                console.warn('SkyBug recent emails RAW response:', txt);
                return;
            }
            
            if(refreshBtn){ 
                refreshBtn.disabled = false; 
                console.log('SKYBUG DEBUG: Re-enabled refresh button');
            }
            // Debug log for diagnose
            try { 
                console.debug('SkyBug recent emails raw parsed object:', data, 'success type=', typeof data?.success); 
            } catch(_e){}
            // WordPress wp_send_json_success wraps payload in data{}
            const hasWrapper = data && typeof data === 'object' && Object.prototype.hasOwnProperty.call(data,'data') && Object.prototype.hasOwnProperty.call(data,'success');
            const payload = hasWrapper ? (data.data || {}) : data;
            const successVal = hasWrapper ? data.success : (data && data.success);
            const isSuccess = successVal === true || successVal === 1 || successVal === 'true' || successVal === '1';
            console.log('SKYBUG DEBUG: Parsed response - hasWrapper=', hasWrapper, 'successVal=', successVal, 'isSuccess=', isSuccess);
            console.log('SKYBUG DEBUG: Payload emails count=', Array.isArray(payload.emails) ? payload.emails.length : 'not array');
            // Debug hook: lagre siste objekt globalt for inspeksjon
            try { window._skybugLastEmailsData = { raw: txt, parsed: data, payload, successVal, isSuccess }; } catch(_e){}
            if(!isSuccess){
                console.log('SKYBUG DEBUG: Request not successful, showing error');
                const attempts = payload?.attempts || payload?.data?.attempts;
                let diag='';
                if(Array.isArray(attempts)){
                    const ok = attempts.filter(a=>a.success).length;
                    diag = ' ('+ok+'/'+attempts.length+' forsøk)';
                }
                recentList.innerHTML = '<li style="color:#dc3545">'+(skybugBugs?.i18n?.imapError||'Feil ved henting')+diag+'</li>';
                return;
            }
            const emails = payload.emails || [];
            console.log('SKYBUG DEBUG: Success! Processing', emails.length, 'emails');
            if(!emails.length){
                console.log('SKYBUG DEBUG: No emails to display');
                const total = payload.total || 0;
                recentList.innerHTML = '<li style="color:#6c757d;font-style:italic">'+(skybugBugs?.i18n?.recentEmailsEmpty||'Ingen e-poster')+' (total='+total+')</li>';
                return;
            }
            console.log('SKYBUG DEBUG: Building email list HTML');
            recentList.innerHTML = emails.map((e, index) => {
                console.log('SKYBUG DEBUG: Processing email', index, '- subject type:', typeof e.subject, 'value:', e.subject);
                console.log('SKYBUG DEBUG: Email', index, 'data:', e);
                const uidAttr = e.uid ? ' data-uid="'+escapeHtml(e.uid)+'"' : '';
                const snippet = e.snippet ? '<div class="skybug-email-snippet" style="margin-top:4px;color:#444;font-size:12px;line-height:1.4;display:none">'+escapeHtml(e.snippet)+'</div>' : '';
                const from = e.from ? '<span style="color:#555;font-size:11px;display:block;margin-top:2px">'+escapeHtml(e.from)+'</span>' : '';
                return '<li class="skybug-recent-email"'+uidAttr+' style="padding:6px 0;border-bottom:1px solid #eef2f5;cursor:pointer">'+
                    '<div class="skybug-email-header">'+
                        '<span class="skybug-email-subject" style="display:block;font-weight:500">'+escapeHtml(e.subject)+'</span>'+
                        (e.date?'<span style="color:#6c757d;font-size:11px">'+escapeHtml(e.date)+'</span>':'')+
                        from+
                        '<div class="skybug-email-actions" style="margin-top:4px;display:none;gap:6px">'+
                            '<button type="button" class="button button-small skybug-email-convert" data-type="bug">Konverter til Bug</button>'+
                            '<button type="button" class="button button-small skybug-email-convert" data-type="feature">Konverter til Ønsket</button>'+
                            '<button type="button" class="button button-small skybug-email-delete" style="color:#a00">Slett</button>'+
                        '</div>'+
                    '</div>'+ snippet +
                '</li>';
            }).join('');
            console.log('SKYBUG DEBUG: Email list HTML built, setting innerHTML');
            // Legg til interaksjon
            qsa('.skybug-recent-email', recentList).forEach(item=>{
                item.addEventListener('click', function(e){
                    if(e.target.closest('button')) return; // actions handled separately
                    const snippetEl = this.querySelector('.skybug-email-snippet');
                    const actionsEl = this.querySelector('.skybug-email-actions');
                    if(snippetEl){ snippetEl.style.display = snippetEl.style.display==='none' ? 'block':'none'; }
                    if(actionsEl){ actionsEl.style.display = actionsEl.style.display==='none' ? 'flex':'none'; }
                });
            });
            // Konverter knapper
            qsa('.skybug-email-convert', recentList).forEach(btn=>{
                btn.addEventListener('click', function(e){
                    e.stopPropagation();
                    const li = this.closest('.skybug-recent-email');
                    const uid = li?.getAttribute('data-uid');
                    if(!uid){ alert('UID mangler'); return; }
                    const subject = li.querySelector('.skybug-email-subject')?.textContent || '';
                    const snippet = li.querySelector('.skybug-email-snippet')?.textContent || '';
                    const from = li.querySelector('.skybug-email-header span[style*="color:#555"]')?.textContent || '';
                    const issueType = this.getAttribute('data-type');
                    this.disabled = true; this.textContent = 'Konverterer...';
                    const fd2 = new FormData();
                    fd2.append('action','skybug_convert_imap_email');
                    fd2.append('nonce', (skybugBugs?.nonces?.imapConvert||''));
                    fd2.append('uid', uid);
                    fd2.append('subject', subject);
                    fd2.append('snippet', snippet);
                    fd2.append('from', from);
                    fd2.append('issue_type', issueType);
                    fetch(ajaxurl,{method:'POST',body:fd2}).then(r=>r.json()).then(data=>{
                        if(data && data.success){
                            this.textContent='Opprettet';
                            if(data.post_id){ window.open(data.edit_link || ('post.php?post='+data.post_id+'&action=edit'),'_blank'); }
                        } else {
                            this.disabled=false; this.textContent='Feil'; setTimeout(()=>{this.textContent='Konverter';},1500);
                        }
                    }).catch(()=>{ this.disabled=false; this.textContent='Feil'; setTimeout(()=>{this.textContent='Konverter';},1500); });
                });
            });
            // Slett knapper
            qsa('.skybug-email-delete', recentList).forEach(btn=>{
                btn.addEventListener('click', function(e){
                    e.stopPropagation();
                    if(!confirm('Slette e-post (kan ikke angres)?')) return;
                    const li = this.closest('.skybug-recent-email');
                    const uid = li?.getAttribute('data-uid');
                    if(!uid){ alert('UID mangler'); return; }
                    this.disabled = true; this.textContent='Sletter...';
                    const fd3 = new FormData();
                    fd3.append('action','skybug_delete_imap_email');
                    fd3.append('nonce', (skybugBugs?.nonces?.imapDelete||''));
                    fd3.append('uid', uid);
                    fetch(ajaxurl,{method:'POST',body:fd3}).then(r=>r.json()).then(data=>{
                        if(data && data.success){ li.remove(); }
                        else { this.disabled=false; this.textContent='Feil'; setTimeout(()=>{this.textContent='Slett';},1500); }
                    }).catch(()=>{ this.disabled=false; this.textContent='Feil'; setTimeout(()=>{this.textContent='Slett';},1500); });
                });
            });
        }).catch(err=>{
            console.log('SKYBUG DEBUG: Fetch error occurred:', err);
            if(refreshBtn){ refreshBtn.disabled = false; }
            recentList.innerHTML = '<li style="color:#dc3545">'+(skybugBugs?.i18n?.imapError||'Feil ved henting')+'</li>';
        });
    }
    function escapeHtml(str){
        if (str === null || str === undefined) return '';
        if (typeof str !== 'string') str = String(str);
        return str.replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]));
    }
    refreshBtn && refreshBtn.addEventListener('click', loadRecentEmails);
    console.log('SKYBUG DEBUG: About to call initial loadRecentEmails, recentList exists =', !!recentList);
    if(recentList){ 
        console.log('SKYBUG DEBUG: Calling initial loadRecentEmails()');
        loadRecentEmails(); 
    }

    // Eksponer funksjon globalt for debugging / manuell refresh
    window.skybug = window.skybug || {};
    window.skybug.loadRecentEmails = loadRecentEmails;
    console.log('SKYBUG DEBUG: bugs-admin.js initialization completed');
})();