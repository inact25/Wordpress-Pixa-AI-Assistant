# Pixa AI Assistant

![Version](https://img.shields.io/badge/version-2.2.2-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL%20v2%2B-green.svg)

AI-powered writing assistant for WordPress using Google Gemini to generate content, analyze articles, and optimize for SEO.

![Pixa AI Assistant](https://www.javapixa.com/_next/image?url=%2F_next%2Fstatic%2Fmedia%2Flogo_symbol.d3d80f6b.png&w=256&q=75)

## 🌟 Features

### ✍️ Content Generation
- **AI-Powered Writing** - Generate blog posts from simple descriptions
- **Multiple Tones** - Professional, Casual, Humorous, Educational, Inspirational, Persuasive, Formal, Friendly
- **Multi-Language Support** - Indonesian (Bahasa Indonesia) & English
- **HTML Output** - Ready-to-use formatted content

### 📊 Article Analysis
- **Quality Assessment** - Get comprehensive analysis of your articles
- **Improvement Recommendations** - Specific suggestions to enhance content
- **Missing Elements Detection** - Identify topics that should be added
- **SEO & Readability Feedback** - Optimize for search engines and readers
- **Structure Analysis** - Improve content flow and organization

### 🚀 SEO Optimization
- **Automated SEO Enhancement** - Improve existing articles for better rankings
- **Keyword Integration** - Add relevant keywords naturally
- **Readability Improvements** - Make content more engaging
- **Header Optimization** - Enhance structure with proper headings

### 🎨 User Interface
- **Floating Button** - Elegant animated button with JavaPixa branding
- **Modern Modal Design** - Clean, professional interface
- **Responsive Layout** - Works perfectly on desktop and mobile
- **Real-time Feedback** - Loading states, error messages, retry notifications

### 🔒 Security & Performance
- **API Key Protection** - Secure header-based authentication
- **Rate Limiting** - Prevents API abuse (10-second cooldown)
- **Content Validation** - 50KB maximum content size
- **Automatic Retry** - Handles temporary API overload (up to 2 retries)
- **Error Logging** - Debug mode support for troubleshooting

## 📋 Requirements

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **Google Gemini API Key:** [Get one here](https://makersuite.google.com/app/apikey)

## 🚀 Installation

1. **Download the plugin**
   ```bash
   cd wp-content/plugins/
   ```

2. **Upload to WordPress**
   - Go to `Plugins > Add New > Upload Plugin`
   - Select the plugin ZIP file
   - Click "Install Now"

3. **Activate the plugin**
   - Go to `Plugins > Installed Plugins`
   - Find "Pixa AI Assistant"
   - Click "Activate"

4. **Configure API Key**
   - Go to `Settings > Pixa AI`
   - Enter your Gemini API key
   - Select your preferred AI model (default: Gemini 2.5 Flash)
   - Click "Save Settings"

## 🎯 Usage

### Generate Content

1. **Open Post/Page Editor** (Classic or Gutenberg)
2. **Click the floating Pixa AI button** (bottom-right corner)
3. **Select "Generate Content" tab**
4. **Choose your options:**
   - Language: Indonesian or English
   - Tone: Professional, Casual, etc.
5. **Describe what you want to write about**
6. **Click "Generate Content"**
7. **Review the result** and click "Insert to Editor"

### Analyze Article

1. **Write your article** in the WordPress editor
2. **Click the Pixa AI button**
3. **Select "Analyze Article" tab**
4. **Click "Analyze Current Article"**
5. **Review the comprehensive analysis:**
   - Overall quality assessment
   - Strengths and weaknesses
   - Recommendations for improvement
   - Missing elements
   - SEO feedback

### Optimize for SEO

1. **Write your article** in the WordPress editor
2. **Click the Pixa AI button**
3. **Select "Optimize for SEO" tab**
4. **Click "Optimize Current Content"**
5. **Review the optimized version**
6. **Click "Insert to Editor"** to replace or add content

## ⚙️ Configuration

### Available Gemini Models

| Model | Description | Speed | Quality |
|-------|-------------|-------|---------|
| **Gemini 2.5 Flash** | Default - Best balance | ⚡⚡⚡ | ⭐⭐⭐⭐ |
| Gemini 2.5 Pro | Highest quality | ⚡⚡ | ⭐⭐⭐⭐⭐ |
| Gemini 2.5 Flash Lite | Fastest | ⚡⚡⚡⚡ | ⭐⭐⭐ |
| Gemini 2.0 Flash | Fast & efficient | ⚡⚡⚡ | ⭐⭐⭐⭐ |
| Gemini 1.5 Pro | Previous gen quality | ⚡⚡ | ⭐⭐⭐⭐ |
| Gemini 1.5 Flash | Previous gen speed | ⚡⚡⚡ | ⭐⭐⭐ |

### Tone Options

- **Professional** - Business and formal content
- **Casual** - Friendly, conversational style
- **Humorous** - Light-hearted and entertaining
- **Educational** - Teaching and informative
- **Inspirational** - Motivational and uplifting
- **Persuasive** - Convincing and compelling
- **Formal** - Academic and serious
- **Friendly** - Warm and approachable

### Language Options

- **Indonesian (Bahasa Indonesia)** - Default language
- **English** - International content

## 🛡️ Security Features

### API Key Protection
- API keys stored securely in WordPress database
- Keys sent via HTTP headers (not URL query strings)
- No exposure in server logs or browser history

### Rate Limiting
- 10-second cooldown between requests per user
- Prevents API abuse and excessive costs
- Automatic enforcement at backend level

### Content Validation
- Maximum prompt length: 5,000 characters
- Maximum content length: 50KB (50,000 characters)
- Input sanitization on all user data
- Proper WordPress nonce verification

### Error Handling
- Automatic retry on temporary failures (503 errors)
- Exponential backoff: 1s → 2s
- User-friendly error messages
- Debug logging when WP_DEBUG is enabled

## 🎨 Customization

### Color Scheme
The plugin uses a crimson and dark theme:
- **Primary:** `#dc143c` (Crimson)
- **Dark:** `#101726`
- **Secondary:** `#3d81f5` (Blue)

### Floating Button Position
Default position: bottom-right corner (30px from edges)

To customize, edit `assets/css/admin-style.css`:
```css
.gwa-floating-btn {
    bottom: 30px; /* Adjust vertical position */
    right: 30px;  /* Adjust horizontal position */
}
```

## 🔧 Troubleshooting

### "API key not configured" Error
**Solution:** Go to `Settings > Pixa AI` and add your Gemini API key.

### "The model is overloaded" Error
**Solution:** The plugin automatically retries (2 attempts). If it still fails, wait a few minutes and try again.

### "Content is too long" Error
**Solution:** Reduce your article length to under 50KB (~50,000 characters).

### Floating Button Not Appearing
**Solution:** 
1. Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
2. Check if you're on a post/page edit screen
3. Ensure the plugin is activated

### Content Not Inserting
**Solution:**
1. For Gutenberg: Plugin uses block parser
2. For Classic Editor: Content is appended to current content
3. Try switching editor modes if issues persist

## 📊 Usage Tracking

The plugin tracks API usage per user for monitoring:
- Number of content generations
- Number of article analyses
- Number of SEO optimizations

**Note:** This data is stored locally and not sent anywhere.

## 🔄 Automatic Retry Logic

When the API returns a 503 (overloaded) error, the plugin automatically:
1. **First retry** - Waits 1 second, then retries
2. **Second retry** - Waits 2 seconds, then retries
3. **Shows error** - If still failing after 2 retries

**User sees:** Yellow notification with countdown during retries.

## 📱 Mobile Support

The plugin is fully responsive:
- Floating button adjusts size on mobile (50x50px)
- Modal adapts to screen width (95% on mobile)
- Form fields stack vertically on small screens
- Touch-friendly buttons and controls

## 🌐 Browser Support

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Opera 76+

## 📝 Changelog

### Version 2.2.2 (Current)
- ✨ Added animated rotating logo on floating button
- 🎨 Logo rotates from -45° to 360° on hover
- ⚡ Smooth 0.6s animation transition

### Version 2.2.1
- 🎨 Replaced star icon with JavaPixa logo
- 🔧 Added white filter for logo visibility

### Version 2.2.0
- 🌐 Added language selection (Indonesian/English)
- 📝 Indonesian set as default language
- 🎨 Two-column layout for Language + Tone selectors
- 📱 Responsive form layout for mobile

### Version 2.1.0
- 🔄 Automatic retry with exponential backoff
- ⏱️ Retry delays: 1s → 2s
- 💬 User-friendly retry notifications
- 🎯 Smart error detection for 503 errors

### Version 2.0.2
- 🐛 Fixed loading state hiding tab content
- ✨ Clean loading experience

### Version 2.0.1
- 🐛 Fixed tab visibility after operations
- 🎨 Form hides when results show
- 🔧 Improved tab switching logic

### Version 2.0.0
- 🔒 API key sent via headers (security fix)
- ⏱️ Rate limiting (10-second cooldown)
- 📏 Content length validation (50KB max)
- 📝 Usage tracking per user
- 🐛 Error logging support
- ⏰ Increased timeout to 60 seconds
- 🌍 Internationalization (i18n) support
- 📊 Improved error messages

### Version 1.x
- 🎉 Initial release
- ✍️ Content generation
- 📊 Article analysis
- 🚀 SEO optimization
- 🎨 Modern UI with floating button

## 🤝 Support

For support, please contact:
- **Website:** [https://javapixa.com](https://javapixa.com)
- **Email:** support@javapixa.com

## 👨‍💻 Developer

**Javapixa Creative Studio**
- Website: [https://javapixa.com](https://javapixa.com)
- Professional web development and creative solutions

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024 Javapixa Creative Studio

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 🙏 Credits

- **Google Gemini API** - AI content generation
- **WordPress** - Content management system
- **JavaPixa** - Plugin development and design

## 🚀 Future Roadmap

- [ ] More language support (Spanish, French, etc.)
- [ ] Bulk content generation
- [ ] Custom tone creation
- [ ] Content scheduling
- [ ] Advanced SEO scoring
- [ ] Grammar and spelling check
- [ ] Plagiarism detection
- [ ] Export/Import settings

---

**Made with ❤️ by [Javapixa Creative Studio](https://javapixa.com)**
