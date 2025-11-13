(function($) {
    'use strict';

    $(document).ready(function() {
        // Check if pixaAiData is defined
        if (typeof pixaAiData === 'undefined') {
            console.error('Pixa AI: Configuration not loaded');
            return;
        }

        if (!pixaAiData.hasApiKey) {
            console.warn('Pixa AI: Gemini API key not configured');
        }

        const floatingButton = `
            <div id="gwa-floating-button" class="gwa-floating-btn">
                <img src="https://www.javapixa.com/_next/image?url=%2F_next%2Fstatic%2Fmedia%2Flogo_symbol.d3d80f6b.png&w=256&q=75" alt="Pixa AI" class="gwa-floating-icon" />
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
                            <button class="gwa-tab-btn" data-tab="image">Generate Image</button>
                            <button class="gwa-tab-btn" data-tab="analyze">Analyze Article</button>
                            <button class="gwa-tab-btn" data-tab="optimize">Optimize for SEO</button>
                        </div>
                        
                        <div id="gwa-generate-tab" class="gwa-tab-content active">
                            <div class="gwa-form-row">
                                <div class="gwa-form-group gwa-form-group-half">
                                    <label for="gwa-language">Language:</label>
                                    <select id="gwa-language" class="gwa-select">
                                        <option value="indonesian" selected>Indonesian (Bahasa Indonesia)</option>
                                        <option value="english">English</option>
                                    </select>
                                </div>
                                <div class="gwa-form-group gwa-form-group-half">
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
                            </div>
                            <div class="gwa-form-group">
                                <label for="gwa-prompt">Describe what you want to write about:</label>
                                <textarea id="gwa-prompt" rows="5" placeholder="Example: Write about the benefits of WordPress plugins for small businesses..."></textarea>
                            </div>
                            <button id="gwa-generate-btn" class="gwa-btn gwa-btn-primary">Generate Content</button>
                        </div>
                        
                        <div id="gwa-image-tab" class="gwa-tab-content">
                            ${!pixaAiData.hasApiKey ? `
                            <div class="gwa-warning-box" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                                <p style="margin: 0; color: #856404;"><strong>⚠️ Gemini API Key Required</strong></p>
                                <p style="margin: 8px 0 0 0; color: #856404;">To use the image generator, you need to configure your Gemini API key in <a href="options-general.php?page=gemini-writing-assistant" style="color: #856404; text-decoration: underline;">Settings > Pixa AI</a></p>
                            </div>
                            ` : ''}
                            <div class="gwa-info-box">
                                <p><strong>AI Image Generator (Nano Banana 🍌)</strong></p>
                                <p>Create unique images using Gemini 2.5 Flash Image model. The generator will:</p>
                                <ul style="list-style-type: disc;">
                                    <li>Generate high-quality images with cinematic quality</li>
                                    <li>Support detailed and creative prompts</li>
                                    <li>Use advanced AI with temperature and creativity controls</li>
                                    <li>Provide ready-to-use PNG images for your content</li>
                                </ul>
                            </div>
                            <div class="gwa-form-group">
                                <label for="gwa-image-prompt">Describe the image you want to generate:</label>
                                <textarea id="gwa-image-prompt" rows="5" placeholder="Example: A beautiful sunset over mountains with vibrant orange and purple colors, photorealistic style..."></textarea>
                            </div>
                            <button id="gwa-generate-image-btn" class="gwa-btn gwa-btn-primary">Generate Image</button>
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

            // Hide all tabs first
            $('.gwa-tab-content').removeClass('active').hide();

            // Show and activate the selected tab
            $('#gwa-' + tab + '-tab').addClass('active').show();

            // Hide result, error, and loading panels
            $('#gwa-result').hide();
            $('#gwa-error').hide();
            $('#gwa-loading').hide();
        });

        $('#gwa-generate-btn').on('click', function() {
            const prompt = $('#gwa-prompt').val().trim();
            const tone = $('#gwa-tone').val();
            const language = $('#gwa-language').val();

            if (!prompt) {
                showError(pixaAiData.strings.error_prompt_required);
                return;
            }

            if (!pixaAiData.hasApiKey) {
                showError(pixaAiData.strings.error_api_key);
                return;
            }

            generateContent(prompt, tone, language);
        });

        $('#gwa-generate-image-btn').on('click', function() {
            const prompt = $('#gwa-image-prompt').val().trim();

            if (!prompt) {
                showError('Please enter a description for the image you want to generate.');
                return;
            }

            if (!pixaAiData.hasApiKey) {
                showError(pixaAiData.strings.error_api_key);
                return;
            }

            generateImage(prompt);
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
            const imageData = $('#gwa-result-content').data('imageData');
            
            if (imageData) {
                // Insert image
                const imagePrompt = $('#gwa-result-content').data('imagePrompt') || 'AI Generated Image';
                insertImageToEditor(imageData, imagePrompt);
            } else {
                // Insert text content
                const content = $('#gwa-result-content').html();
                insertToEditor(content);
            }
            
            $('#gwa-modal').fadeOut(200);
            resetModal();
        });

        function generateContent(prompt, tone, language, retryCount) {
            retryCount = retryCount || 0;
            const maxRetries = 2;
            
            showLoading();

            $.ajax({
                url: pixaAiData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'gwa_generate_content',
                    nonce: pixaAiData.nonce,
                    prompt: prompt,
                    tone: tone,
                    language: language || 'indonesian'
                },
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        showResult(response.data.content);
                    } else {
                        const errorMsg = response.data && response.data.message ? response.data.message : 'An error occurred';
                        
                        // Check if it's a retryable error (503, overloaded)
                        if (retryCount < maxRetries && errorMsg.includes('overloaded')) {
                            const delay = Math.pow(2, retryCount) * 1000; // Exponential backoff: 1s, 2s
                            showRetryMessage('API is busy. Retrying in ' + (delay/1000) + ' seconds...');
                            setTimeout(function() {
                                generateContent(prompt, tone, language, retryCount + 1);
                            }, delay);
                        } else {
                            showError(errorMsg);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    showError('Network error: ' + error);
                }
            });
        }

        function generateImage(prompt, retryCount) {
            retryCount = retryCount || 0;
            const maxRetries = 2;
            
            showLoading();

            $.ajax({
                url: pixaAiData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'gwa_generate_image',
                    nonce: pixaAiData.nonce,
                    prompt: prompt
                },
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        showImageResult(response.data.image, prompt);
                    } else {
                        const errorMsg = response.data && response.data.message ? response.data.message : 'An error occurred';
                        
                        // Check if it's a retryable error
                        if (retryCount < maxRetries && errorMsg.includes('overloaded')) {
                            const delay = Math.pow(2, retryCount) * 1000;
                            showRetryMessage('API is busy. Retrying in ' + (delay/1000) + ' seconds...');
                            setTimeout(function() {
                                generateImage(prompt, retryCount + 1);
                            }, delay);
                        } else {
                            showError(errorMsg);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    showError('Network error: ' + error);
                }
            });
        }

        function analyzeContent(content, retryCount) {
            retryCount = retryCount || 0;
            const maxRetries = 2;
            
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
                        
                        // Check if it's a retryable error
                        if (retryCount < maxRetries && errorMsg.includes('overloaded')) {
                            const delay = Math.pow(2, retryCount) * 1000;
                            showRetryMessage('API is busy. Retrying in ' + (delay/1000) + ' seconds...');
                            setTimeout(function() {
                                analyzeContent(content, retryCount + 1);
                            }, delay);
                        } else {
                            showError(errorMsg);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    showError('Network error: ' + error);
                }
            });
        }

        function optimizeContent(content, retryCount) {
            retryCount = retryCount || 0;
            const maxRetries = 2;
            
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
                        showResult(response.data.content, 'Optimized Content');
                    } else {
                        const errorMsg = response.data && response.data.message ? response.data.message : 'An error occurred';
                        
                        // Check if it's a retryable error
                        if (retryCount < maxRetries && errorMsg.includes('overloaded')) {
                            const delay = Math.pow(2, retryCount) * 1000;
                            showRetryMessage('API is busy. Retrying in ' + (delay/1000) + ' seconds...');
                            setTimeout(function() {
                                optimizeContent(content, retryCount + 1);
                            }, delay);
                        } else {
                            showError(errorMsg);
                        }
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

        function insertImageToEditor(imageData, altText) {
            if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch('core/editor')) {
                // For Gutenberg editor, create an image block
                const imageBlock = wp.blocks.createBlock('core/image', {
                    url: imageData,
                    alt: altText,
                    caption: ''
                });
                const currentBlocks = wp.data.select('core/editor').getBlocks();
                wp.data.dispatch('core/editor').insertBlocks(imageBlock, currentBlocks.length);
            } else if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                // For classic editor
                const imageHtml = '<img src="' + imageData + '" alt="' + altText + '" style="max-width: 100%; height: auto;" />';
                tinymce.activeEditor.insertContent(imageHtml);
            } else if ($('#content').length) {
                // For plain textarea
                const imageHtml = '<img src="' + imageData + '" alt="' + altText + '" style="max-width: 100%; height: auto;" />';
                const currentContent = $('#content').val();
                $('#content').val(currentContent + '\n\n' + imageHtml);
            }
        }

        function showLoading() {
            // Hide everything and show only loading
            $('.gwa-tab-content').hide();
            $('#gwa-result').hide();
            $('#gwa-error').hide();
            $('#gwa-loading').show();
        }

        function hideLoading() {
            $('#gwa-loading').hide();
        }

        function showResult(content, title) {
            let formattedContent = content.trim();

            if (formattedContent.startsWith('```html')) {
                formattedContent = formattedContent.replace(/^```html\n?/, '').replace(/```$/, '').trim();
            } else if (formattedContent.startsWith('```')) {
                formattedContent = formattedContent.replace(/^```\n?/, '').replace(/```$/, '').trim();
            }

            $('#gwa-result-header h3').text(title || 'Generated Content');
            $('#gwa-insert-btn').show();
            $('#gwa-result-content').html(formattedContent);

            // Hide all tab content forms and show result
            $('.gwa-tab-content').hide();
            $('#gwa-result').show();
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

            // Hide all tab content forms and show result
            $('.gwa-tab-content').hide();
            $('#gwa-result').show();
        }

        function showImageResult(imageData, prompt) {
            const imageHtml = '<div class="gwa-image-preview">' +
                '<img src="' + imageData + '" alt="Generated Image" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" />' +
                '<p style="margin-top: 10px; color: #666; font-style: italic;">Prompt: ' + prompt + '</p>' +
                '</div>';

            $('#gwa-result-header h3').text('Generated Image');
            $('#gwa-insert-btn').text('Insert Image to Editor').show();
            $('#gwa-result-content').html(imageHtml);

            // Store image data for insertion
            $('#gwa-result-content').data('imageData', imageData);
            $('#gwa-result-content').data('imagePrompt', prompt);

            // Hide all tab content forms and show result
            $('.gwa-tab-content').hide();
            $('#gwa-result').show();
        }

        function showError(message) {
            $('#gwa-error').html('<strong>Error:</strong> ' + message).show();
            // Show the active tab when error occurs
            $('.gwa-tab-content.active').show();
            setTimeout(function() {
                $('#gwa-error').fadeOut();
            }, 5000);
        }

        function showRetryMessage(message) {
            $('#gwa-error').html('<strong>⏳ Retrying:</strong> ' + message)
                .css('background', '#fff3cd')
                .css('color', '#856404')
                .css('border-left-color', '#ffc107')
                .show();
        }

        function resetModal() {
            $('#gwa-prompt').val('');
            $('#gwa-image-prompt').val('');
            $('#gwa-result').hide();
            $('#gwa-error').hide();
            $('#gwa-loading').hide();
            $('#gwa-result-content').removeData('imageData').removeData('imagePrompt');
            $('#gwa-insert-btn').text('Insert to Editor');
        }
    });

})(jQuery);
