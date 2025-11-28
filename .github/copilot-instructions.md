# Copilot Instructions for Providence Frontend

This repository contains the Providence Financial Platform Frontend, a mobile-web investment application built with HTML, CSS, and JavaScript.

## Project Overview

- **Name**: Providence Financial Platform Frontend
- **Tech Stack**: HTML, CSS, JavaScript (ES2022+)
- **Package Manager**: npm
- **Linting**: ESLint 9.x

## Development Guidelines

### Code Style

- Use ES2022+ JavaScript features with ESModule syntax
- Follow the ESLint configuration in `eslint.config.js`
- Use single quotes for strings
- Use 2-space indentation
- Add semicolons at the end of statements
- Avoid trailing spaces
- Files should end with a newline

### UI Conventions

- Use `showToast()` from `ios-toast.js` for popup notifications instead of `alert()`
- Use `class="back-btn-policy"` for back buttons
- Use `#0e2b44` (dark blue) for separator lines
- Modal boxes should have `max-height: 70vh`
- All CSS/JS files should include version query parameters (`?v=timestamp`) to bust cache

### API Integration

- API configuration is in `config.js` (`window.API_CONFIG`)
- Use the unified HTTP client available at `window.httpClient` for simple requests
- Use `window.ApiService` for structured API calls (auth, user, ribao, project, points, finance)
- Backend API endpoint is configured in `API_CONFIG.baseURL`

### File Organization

- Main pages are HTML files in the root directory
- JavaScript modules are in the root or `js/` directory
- Styles are in the `css/` directory
- Assets and images are in the `img/` and `assets/` directories

## Commands

- **Lint**: `npm run lint`
- **Dev**: `npm run dev`
- **Build**: `npm run build`

## Important Files

- `config.js` - API configuration
- `eslint.config.js` - ESLint configuration
- `PROJECT_CONTEXT.md` - Detailed project context and history
- `CODE_CHECK_GUIDE.md` - Code checking tool usage guide

## Modification Process

1. **Before modifying**: Check parent references and CSS imports
2. **During modification**: Remove old code, use unified CSS classes, avoid duplication
3. **After modification**: Validate HTML/CSS, clean up temporary files, add version numbers
4. **Before publishing**: Force refresh test, check all links, update documentation

## Testing

After making changes, verify them by:
1. Running `npm run lint` to check for code issues
2. Force refreshing the browser (`Ctrl+Shift+R` or `Cmd+Shift+R`) due to CloudFlare CDN caching
