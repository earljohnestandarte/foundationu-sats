(function ($) {
    var realtimeState = {
        socket: null,
        reconnectTimer: null,
        initialized: false,
        refreshing: false
    };

    function getEditorForm(el) {
        return el.closest('form');
    }

    function initializeQuillEditors(root) {
        if (typeof Quill === 'undefined') {
            return;
        }

        var scope = root || document;
        $(scope).find('.quill-editor').each(function () {
            if (this.dataset.quillReady === '1') {
                return;
            }

            var quill = new Quill(this, {
                theme: 'snow',
                modules: { toolbar: [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link']] },
                placeholder: 'Write your message...'
            });

            this.__quill = quill;
            this.dataset.quillReady = '1';
        });
    }

    function syncFormEditor(form) {
        if (!form) {
            return;
        }

        var editorEl = form.querySelector('.quill-editor');
        var hidden = form.querySelector('.quill-hidden');
        if (editorEl && editorEl.__quill && hidden) {
            hidden.value = editorEl.__quill.root.innerHTML;
        }
    }

    function getEditorHtml(form) {
        var editor = form ? form.querySelector('.ql-editor') : null;
        return editor ? editor.innerHTML.trim() : '';
    }

    function setupReplyToggleHandlers() {
        $(document).off('click.ticketReplyToggle').on('click.ticketReplyToggle', '.reply-toggle', function () {
            var target = $(this).data('target');
            $('.reply-form').not('.' + target).hide();
            $('.' + target).toggle();
        });
    }

    function setupSubmitHandlers() {
        $(document).off('submit.ticketQuillSync').on('submit.ticketQuillSync', 'form', function () {
            syncFormEditor(this);
        });

        $(document).off('click.ticketQuillSubmit').on('click.ticketQuillSubmit', '.quill-submit', function (e) {
            var form = this.closest('form');
            if (getEditorHtml(form) === '<p><br></p>' || getEditorHtml(form) === '') {
                e.preventDefault();
                alert('Please enter a message.');
                return;
            }

            syncFormEditor(form);
        });
    }

    function setupAiSuggestHandler() {
        $(document).off('click.ticketAiSuggest').on('click.ticketAiSuggest', '.ai-suggest-btn', function () {
            var btn = $(this);
            var ticketId = btn.data('ticket');
            var form = btn.closest('form');
            var quillEl = form.find('.quill-editor')[0];

            if (!quillEl || !quillEl.__quill) {
                alert('Editor not ready. Please refresh the page.');
                return;
            }

            var quill = quillEl.__quill;
            var existingText = quill.getText().trim();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Suggesting...');

            var url = siteUrl('ai/suggest/' + ticketId);
            if (existingText) {
                url += '?current_text=' + encodeURIComponent(existingText);
            }

            $.getJSON(url, function (data) {
                if (data.success && data.suggestion) {
                    var html = data.suggestion
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.+?)\*/g, '<em>$1</em>')
                        .replace(/^Subject:.*$/gm, '')
                        .replace(/^(Dear|Hi|Hey|Hello)\b.*$/gm, '')
                        .replace(/^(Sincerely|Regards|Warm regards|Best|Cheers|Thank you),?\s*$/gmi, '')
                        .replace(/^\[?Your Name\]?\s*$/gmi, '')
                        .replace(/^[A-Za-z ]+,\s*$/gm, '')
                        .replace(/^Student Affairs Assistant,?\s*$/gmi, '')
                        .replace(/^Foundation University,?\s*$/gmi, '')
                        .replace(/^\[Phone\].*$/gmi, '')
                        .replace(/^\[Email\].*$/gmi, '')
                        .replace(/\n{3,}/g, '\n\n')
                        .trim();

                    html = '<p>' + html
                        .replace(/\n\n/g, '</p><p>')
                        .replace(/\n/g, '<br>') + '</p>';

                    html = html.replace(/<p>\s*<\/p>/g, '');

                    quill.clipboard.dangerouslyPasteHTML(quill.getLength(), html);
                    quill.setSelection(quill.getLength(), 0);
                } else if (data.message) {
                    alert(data.message);
                }
            }).fail(function () {
                alert('Unable to generate suggestion. Please try again.');
            }).always(function () {
                btn.prop('disabled', false).html('<i class="fas fa-magic"></i> AI Suggest');
            });
        });
    }

    function setupAgentReplyComposer() {
        var config = window.ticketRealtimeConfig || {};
        if (!config.agentMode) {
            return;
        }

        var toggleBtn = document.getElementById('internalToggle');
        var internalInput = document.getElementById('isInternalInput');
        var replyForm = document.getElementById('main-reply-form');

        if (toggleBtn && !toggleBtn.dataset.boundToggle) {
            toggleBtn.dataset.boundToggle = '1';
            toggleBtn.addEventListener('click', function () {
                var active = toggleBtn.classList.toggle('active');
                if (internalInput) {
                    internalInput.value = active ? '1' : '0';
                }
                if (replyForm) {
                    replyForm.classList.toggle('is-internal', active);
                }
                toggleBtn.innerHTML = active
                    ? '<i class="fas fa-lock me-1"></i> Internal Note <i class="fas fa-check ms-1"></i>'
                    : '<i class="fas fa-lock me-1"></i> Internal Note';
            });
        }

        var replyDropzone = document.getElementById('reply-dropzone');
        var replyFileInput = document.getElementById('reply-attachments');
        var replyPreview = document.getElementById('replyFilePreview');
        if (!replyDropzone || !replyFileInput || !replyPreview || replyDropzone.dataset.boundDropzone === '1') {
            return;
        }

        replyDropzone.dataset.boundDropzone = '1';
        var replyFiles = [];

        function formatSize(bytes) {
            if (bytes < 1024) {
                return bytes + ' B';
            }
            if (bytes < 1048576) {
                return (bytes / 1024).toFixed(1) + ' KB';
            }
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        function getIcon(name) {
            var ext = name.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) !== -1) {
                return 'fa-file-image';
            }
            if (ext === 'pdf') {
                return 'fa-file-pdf';
            }
            if (['doc', 'docx'].indexOf(ext) !== -1) {
                return 'fa-file-word';
            }
            return 'fa-file';
        }

        function renderReplyChips() {
            replyPreview.innerHTML = '';
            replyFiles.forEach(function (file, index) {
                var chip = document.createElement('div');
                chip.className = 'fu-file-chip';
                chip.innerHTML = '<i class="fas ' + getIcon(file.name) + '"></i><span class="fu-chip-name">' + file.name + '</span><span class="fu-chip-size">' + formatSize(file.size) + '</span><button type="button" class="fu-chip-remove" data-idx="' + index + '"><i class="fas fa-times"></i></button>';
                replyPreview.appendChild(chip);
            });

            var transfer = new DataTransfer();
            replyFiles.forEach(function (file) {
                transfer.items.add(file);
            });
            replyFileInput.files = transfer.files;
        }

        function addFiles(fileList) {
            Array.from(fileList).forEach(function (file) {
                if (replyFiles.length < 5 && file.size <= 5 * 1024 * 1024) {
                    replyFiles.push(file);
                }
            });
            renderReplyChips();
        }

        replyPreview.addEventListener('click', function (e) {
            var btn = e.target.closest('.fu-chip-remove');
            if (!btn) {
                return;
            }
            replyFiles.splice(parseInt(btn.dataset.idx, 10), 1);
            renderReplyChips();
        });

        replyFileInput.addEventListener('change', function () {
            addFiles(replyFileInput.files);
        });

        replyDropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            replyDropzone.classList.add('dragover');
        });

        replyDropzone.addEventListener('dragleave', function () {
            replyDropzone.classList.remove('dragover');
        });

        replyDropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            replyDropzone.classList.remove('dragover');
            addFiles(e.dataTransfer.files);
        });
    }

    function refreshTicketThread() {
        var config = window.ticketRealtimeConfig || {};
        if (!config.threadUrl || realtimeState.refreshing) {
            return;
        }

        realtimeState.refreshing = true;

        $.getJSON(config.threadUrl, function (data) {
            if (!data.success) {
                return;
            }

            if (data.repliesHtml) {
                $('#ticketReplyThread').html(data.repliesHtml);
                initializeQuillEditors(document.getElementById('ticketReplyThread'));
            }

            if (data.timelineHtml) {
                $('#ticketTimeline').html(data.timelineHtml);
            }
        }).always(function () {
            realtimeState.refreshing = false;
        });
    }

    function connectRealtime() {
        var config = window.ticketRealtimeConfig || {};
        if (!config.wsUrl || !config.subscription || realtimeState.socket) {
            return;
        }

        try {
            realtimeState.socket = new WebSocket(config.wsUrl);
        } catch (err) {
            realtimeState.socket = null;
            return;
        }

        realtimeState.socket.addEventListener('open', function () {
            realtimeState.socket.send(JSON.stringify($.extend({ action: 'subscribe' }, config.subscription)));
        });

        realtimeState.socket.addEventListener('message', function (event) {
            var payload;
            try {
                payload = JSON.parse(event.data);
            } catch (err) {
                return;
            }

            if (payload.type === 'reply.created' && Number(payload.ticketId) === Number(config.ticketId)) {
                if (Number(payload.actorId) !== Number(config.currentUserId)) {
                    refreshTicketThread();
                }
            }
        });

        realtimeState.socket.addEventListener('close', function () {
            realtimeState.socket = null;
            if (realtimeState.reconnectTimer) {
                window.clearTimeout(realtimeState.reconnectTimer);
            }
            realtimeState.reconnectTimer = window.setTimeout(connectRealtime, 3000);
        });

        realtimeState.socket.addEventListener('error', function () {
            if (realtimeState.socket) {
                realtimeState.socket.close();
            }
        });
    }

    function initializeTicketView(root) {
        initializeQuillEditors(root || document);
        setupAgentReplyComposer();

        if (!realtimeState.initialized) {
            setupReplyToggleHandlers();
            setupSubmitHandlers();
            setupAiSuggestHandler();
            connectRealtime();
            realtimeState.initialized = true;
        }
    }

    window.initializeTicketView = initializeTicketView;

    $(function () {
        initializeTicketView(document);
    });
})(jQuery);
