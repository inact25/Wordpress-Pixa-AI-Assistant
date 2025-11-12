(function($) {
    'use strict';

    $(document).ready(function() {
        if (!gwaData.hasApiKey) {
            console.warn('Gemini API key not configured');
        }

        const floatingButton = `
            <div id="gwa-floating-button" class="gwa-floating-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="white"/>
                </svg>
            </div>
        `;

        const modalHtml = `
            <div id="gwa-modal" class="gwa-modal">
                <div class="gwa-modal-content">
                    <div class="gwa-modal-header">
                        <h2 style="color:white;">Pixa Ai Assistant</h2>
                        <span class="gwa-close">&times;</span>
                    </div>
                    <div class="gwa-modal-body">
                        <div class="gwa-tabs">
                            <button class="gwa-tab-btn active" data-tab="generate">Generate Content</button>
                            <button class="gwa-tab-btn" data-tab="optimize">Optimize for SEO</button>
                        </div>
                        
                        <div id="gwa-generate-tab" class="gwa-tab-content active">
                            <div class="gwa-form-group">
                                <label for="gwa-tone">Tone:</label>
                                <select id="gwa-tone" class="gwa-select">
                                    <option value="professional">Professional</option>
                                    <option value="casual">Casual</option>
                                    <option value="humorous">Humorous</option>
                                    <option value="educational">Educational</option>
                                    <option value="inspirational">Inspirational</option>
                                    <option value="persuasive">Persuasive</option>
                                    <option value="formal">Formal</option>
                                    <option value="friendly">Friendly</option>
                                </select>
                            </div>
                            <div class="gwa-form-group">
                                <label for="gwa-prompt">Describe what you want to write about:</label>
                                <textarea id="gwa-prompt" rows="5" placeholder="Example: Write about the benefits of WordPress plugins for small businesses..."></textarea>
                            </div>
                            <button id="gwa-generate-btn" class="gwa-btn gwa-btn-primary">Generate Content</button>
                        </div>
                        
                        <div id="gwa-optimize-tab" class="gwa-tab-content">
                            <div class="gwa-info-box">
                                <p><strong>SEO Optimization</strong></p>
                                <p>Click the button below to optimize your current article for better SEO performance. The AI will:</p>
                                <ul style="list-style-type: disc;">
                                    <li>Improve readability and structure</li>
                                    <li>Add relevant keywords naturally</li>
                                    <li>Enhance headings and formatting</li>
                                    <li>Make content more engaging</li>
                                </ul>
                            </div>
                            <button id="gwa-optimize-btn" class="gwa-btn gwa-btn-primary">Optimize Current Content</button>
                        </div>
                        
                        <div id="gwa-loading" class="gwa-loading" style="display: none;">
                            <div class="gwa-spinner"></div>
                            <p>AI is working on your request...</p>
                        </div>
                        
                        <div id="gwa-result" class="gwa-result" style="display: none;">
                            <div class="gwa-result-header">
                                <h3>Generated Content</h3>
                                <button id="gwa-insert-btn" class="gwa-btn gwa-btn-success">Insert to Editor</button>
                            </div>
                            <div id="gwa-result-content"></div>
                        </div>
                        
                        <div id="gwa-error" class="gwa-error" style="display: none;"></div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(floatingButton);
        $('body').append(modalHtml);

        $('#gwa-floating-button').on('click', function() {
            $('#gwa-modal').fadeIn(200);
        });

        $('.gwa-close').on('click', function() {
            $('#gwa-modal').fadeOut(200);
            resetModal();
        });

        $(window).on('click', function(e) {
            if ($(e.target).is('#gwa-modal')) {
                $('#gwa-modal').fadeOut(200);
                resetModal();
            }
        });

        $('.gwa-tab-btn').on('click', function() {
            const tab = $(this).data('tab');

            $('.gwa-tab-btn').removeClass('active');
            $(this).addClass('active');

            $('.gwa-tab-content').removeClass('active');
            $('#gwa-' + tab + '-tab').addClass('active');

            resetModal();
        });

        $('#gwa-generate-btn').on('click', function() {
            const prompt = $('#gwa-prompt').val().trim();
            const tone = $('#gwa-tone').val();

            if (!prompt) {
                showError('Please enter a description of what you want to write about.');
                return;
            }

            if (!gwaData.hasApiKey) {
                showError('API key not configured. Please add your Gemini API key in Settings > Pixa AI');
                return;
            }

            generateContent(prompt, tone);
        });

        $('#gwa-optimize-btn').on('click', function() {
            if (!gwaData.hasApiKey) {
                showError('API key not configured. Please add your Gemini API key in Settings > Pixa AI');
                return;
            }

            const content = getEditorContent();

            if (!content) {
                showError('No content found in the editor. Please write something first.');
                return;
            }

            optimizeContent(content);
        });

        $('#gwa-insert-btn').on('click', function() {
            const content = $('#gwa-result-content').html();
            insertToEditor(content);
            $('#gwa-modal').fadeOut(200);
            resetModal();
        });

        function generateContent(prompt, tone) {
            showLoading();

            $.ajax({
                url: gwaData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'gwa_generate_content',
                    nonce: gwaData.nonce,
                    prompt: prompt,
                    tone: tone
                },
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        showResult(response.data.content);
                    } else {
                        showError(response.data || 'An error occurred');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    showError('Network error: ' + error);
                }
            });
        }

        function optimizeContent(content) {
            showLoading();

            $.ajax({
                url: gwaData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'gwa_optimize_seo',
                    nonce: gwaData.nonce,
                    content: content
                },
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        showResult(response.data.content);
                    } else {
                        showError(response.data || 'An error occurred');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    showError('Network error: ' + error);
                }
            });
        }

        function getEditorContent() {
            if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                return wp.data.select('core/editor').getEditedPostContent();
            } else if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                return tinymce.activeEditor.getContent();
            } else if ($('#content').length) {
                return $('#content').val();
            }
            return '';
        }

        function insertToEditor(content) {
            if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch('core/editor')) {
                const blocks = wp.blocks.parse(content);
                const currentBlocks = wp.data.select('core/editor').getBlocks();
                wp.data.dispatch('core/editor').insertBlocks(blocks, currentBlocks.length);
            } else if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                tinymce.activeEditor.insertContent(content);
            } else if ($('#content').length) {
                const currentContent = $('#content').val();
                $('#content').val(currentContent + '\n\n' + content);
            }
        }

        function showLoading() {
            $('#gwa-loading').show();
            $('#gwa-result').hide();
            $('#gwa-error').hide();
            $('.gwa-tab-content').hide();
        }

        function hideLoading() {
            $('#gwa-loading').hide();
        }

        function showResult(content) {
            let formattedContent = content.trim();

            if (formattedContent.startsWith('```html')) {
                formattedContent = formattedContent.replace(/^```html\n?/, '').replace(/```$/, '').trim();
            } else if (formattedContent.startsWith('```')) {
                formattedContent = formattedContent.replace(/^```\n?/, '').replace(/```$/, '').trim();
            }

            $('#gwa-result-content').html(formattedContent);
            $('#gwa-result').show();
        }

        function showError(message) {
            $('#gwa-error').html('<strong>Error:</strong> ' + message).show();
            setTimeout(function() {
                $('#gwa-error').fadeOut();
            }, 5000);
        }

        function resetModal() {
            $('#gwa-prompt').val('');
            $('#gwa-result').hide();
            $('#gwa-error').hide();
            $('#gwa-loading').hide();
        }
    });

})(jQuery);
