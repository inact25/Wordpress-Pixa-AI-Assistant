(function($) {
    'use strict';

    $(document).ready(function() {
        // Check if pixaAiData is defined
        if (typeof pixaAiData === 'undefined') {
            console.error('Pixa AI: Configuration not loaded');
            return;
        }

        if (!pixaAiData.hasApiKey) {
            console.warn('Pixa AI: API key not configured');
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
                            <button class="gwa-tab-btn" data-tab="analyze">Analyze Article</button>
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
                        
                        <div id="gwa-analyze-tab" class="gwa-tab-content">
                            <div class="gwa-info-box">
                                <p><strong>Article Analysis</strong></p>
                                <p>Get AI-powered insights about your current article. The analysis will include:</p>
                                <ul style="list-style-type: disc;">
                                    <li>Overall quality assessment</li>
                                    <li>Recommendations for improvement</li>
                                    <li>Missing elements or topics</li>
                                    <li>Content structure suggestions</li>
                                    <li>SEO and readability feedback</li>
                                </ul>
                            </div>
                            <button id="gwa-analyze-btn" class="gwa-btn gwa-btn-primary">Analyze Current Article</button>
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
            $('.gwa-tab-content.active').show();
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

            $('#gwa-result').hide();
            $('#gwa-error').hide();
            $('#gwa-loading').hide();
        });

        $('#gwa-generate-btn').on('click', function() {
            const prompt = $('#gwa-prompt').val().trim();
            const tone = $('#gwa-tone').val();

            if (!prompt) {
                showError(pixaAiData.strings.error_prompt_required);
                return;
            }

            if (!pixaAiData.hasApiKey) {
                showError(pixaAiData.strings.error_api_key);
                return;
            }

            generateContent(prompt, tone);
        });

        $('#gwa-analyze-btn').on('click', function() {
            if (!pixaAiData.hasApiKey) {
                showError(pixaAiData.strings.error_api_key);
                return;
            }

            const content = getEditorContent();

            if (!content) {
                showError(pixaAiData.strings.error_no_content);
                return;
            }
            
            if (content.length > pixaAiData.maxContentLength) {
                showError(pixaAiData.strings.error_content_too_long);
                return;
            }

            analyzeContent(content);
        });

        $('#gwa-optimize-btn').on('click', function() {
            if (!pixaAiData.hasApiKey) {
                showError(pixaAiData.strings.error_api_key);
                return;
            }

            const content = getEditorContent();

            if (!content) {
                showError(pixaAiData.strings.error_no_content);
                return;
            }
            
            if (content.length > pixaAiData.maxContentLength) {
                showError(pixaAiData.strings.error_content_too_long);
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
                url: pixaAiData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'gwa_generate_content',
                    nonce: pixaAiData.nonce,
                    prompt: prompt,
                    tone: tone
                },
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        showResult(response.data.content);
                    } else {
                        const errorMsg = response.data && response.data.message ? response.data.message : 'An error occurred';
                        showError(errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    showError('Network error: ' + error);
                }
            });
        }

        function analyzeContent(content) {
            showLoading();

            $.ajax({
                url: pixaAiData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'gwa_analyze_content',
                    nonce: pixaAiData.nonce,
                    content: content
                },
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        showAnalysisResult(response.data.analysis);
                    } else {
                        const errorMsg = response.data && response.data.message ? response.data.message : 'An error occurred';
                        showError(errorMsg);
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
                url: pixaAiData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'gwa_optimize_seo',
                    nonce: pixaAiData.nonce,
                    content: content
                },
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        showResult(response.data.content);
                    } else {
                        const errorMsg = response.data && response.data.message ? response.data.message : 'An error occurred';
                        showError(errorMsg);
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
            $('.gwa-tab-content.active').show();
        }

        function showResult(content) {
            let formattedContent = content.trim();

            if (formattedContent.startsWith('```html')) {
                formattedContent = formattedContent.replace(/^```html\n?/, '').replace(/```$/, '').trim();
            } else if (formattedContent.startsWith('```')) {
                formattedContent = formattedContent.replace(/^```\n?/, '').replace(/```$/, '').trim();
            }

            $('#gwa-result-header h3').text('Generated Content');
            $('#gwa-insert-btn').show();
            $('#gwa-result-content').html(formattedContent);
            $('#gwa-result').show();
            $('.gwa-tab-content.active').show();
        }

        function showAnalysisResult(analysis) {
            let formattedAnalysis = analysis.trim();

            if (formattedAnalysis.startsWith('```html')) {
                formattedAnalysis = formattedAnalysis.replace(/^```html\n?/, '').replace(/```$/, '').trim();
            } else if (formattedAnalysis.startsWith('```')) {
                formattedAnalysis = formattedAnalysis.replace(/^```\n?/, '').replace(/```$/, '').trim();
            }

            $('#gwa-result-header h3').text('Article Analysis');
            $('#gwa-insert-btn').hide();
            $('#gwa-result-content').html(formattedAnalysis);
            $('#gwa-result').show();
            $('.gwa-tab-content.active').show();
        }

        function showError(message) {
            $('#gwa-error').html('<strong>Error:</strong> ' + message).show();
            $('.gwa-tab-content.active').show();
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
