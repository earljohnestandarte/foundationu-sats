$(document).ready(function() {
    $('.reply-toggle').on('click', function() {
        var target = $(this).data('target');
        $('.reply-form').not('.' + target).hide();
        $('.' + target).toggle();
    });

    $('.ai-suggest-btn').on('click', function() {
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

        $.getJSON(url, function(data) {
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
                    .replace(/\n/g, '<br>')
                    + '</p>';

                html = html.replace(/<p>\s*<\/p>/g, '');

                quill.clipboard.dangerouslyPasteHTML(quill.getLength(), html);
                quill.setSelection(quill.getLength(), 0);
            } else if (data.message) {
                alert(data.message);
            }
        }).fail(function() {
            alert('Unable to generate suggestion. Please try again.');
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-magic"></i> AI Suggest');
        });
    });
});
