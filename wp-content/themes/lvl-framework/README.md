[www.webmechanix.com](https://www.webmechanix.com)

---

# LVL Framework

## Description

LVL Prime is a WordPress theme built by WebMechanix. It is a parent theme that is used as a starting point for all
WebMechanix WordPress projects.

## Requirements

- [ACF Pro 6.2.1](https://www.advancedcustomfields.com/resources/)
- [Bootstrap 5.3.0](https://getbootstrap.com/docs/5.3/getting-started/introduction/)
- [PHP 8.2](https://www.php.net/manual/en/)
- [WordPress 6.3.1](https://wordpress.org/documentation/)

---

## Table of Contents

- [Getting Started](#getting-started)
- [Workflow](#workflow)
- [Plugins](#plugins)
- [Parent Theme](#parent-theme)
- [Child Theme](#child-theme)

## Getting Started

### Requirements

- [Node.js](https://nodejs.org/en/)
- [NPM](https://www.npmjs.com/)
- [Ruby](https://www.ruby-lang.org/en/)

### NVM Use

If you have NVM installed, you can use the `.nvmrc` file to set the correct version of Node.js for the project.

- `nvm use` - use the correct version of Node.js
- `nvm install` - install the correct version of Node.js

If there is not a `.nvmrc` file, you can use the following command to create one:

- `node -v > .nvmrc` - create a `.nvmrc` file with the current version of Node.js

Preferably, you should use the latest version of Node.js if this is a new project.

- `nvm install node` - install the latest version of Node.js



### NPM Install

The `package.json` file contains all the dependencies for the project. To install all the dependencies run the following
command:

- `npm install` - install all dependencies

### Compiling Assets (CSS & JS)

This project uses Laravel Mix to compile assets. To learn more about Laravel Mix visit
the [documentation](https://laravel-mix.com/docs/6.0/installation).

List of commands to run in order to compile assets:

- `npm run dev` - compiles assets for development
- `npm run watch` - compiles assets for development and watches for changes
- `npm run hot` - compiles assets for development and watches for changes (hot module reloading)
- `npm run production` - compiles assets for production, minifies and uglifies
- `npm run fonts` - compiles fonts, clears and copies to dist
- `npm run webp` - compiles webp images, clears and copies to dist

## Workflow

### Development

- **IF SIGNIFICANT WORK, OR OTHERS ARE WORKING ON REPOSITORY, CREATE A NEW BRANCH!**
- Work locally if possible.
- Commit often.
- Push to repository often.
- Pull from repository often.
- **DO NOT COMMIT ANYTHING IN THE `.gitignore` FILE!**

### Before Deploying to Production

#### <span style="color:red;">DO NOT DEPLOY TO PRODUCTION UNLESS YOU HAVE MERGED INTO MASTER</span>

- If you are working on a branch, merge your branch into master and push to repository
  - Delete your branch after merging into master
- Run `npm run production` to compile assets for production
- If you are working on master, push to repository
- Deploy to production

## Plugins

### Required

- Advanced Custom Fields PRO (license key on file) http://www.advancedcustomfields.com/resources/

### Common

- [Gravity Forms](https://docs.gravityforms.com/)
- [Yoast SEO](https://developer.yoast.com/code-documentation/)
- [Yoast Duplicate Post](https://wordpress.org/plugins/duplicate-post/)
- [Redirection](https://wordpress.org/plugins/redirection/)
- [WebP Express](https://wordpress.org/plugins/webp-express/)
- [Admin Columns Pro](https://www.admincolumns.com/documentation/)

### Others

- [SVG Support](https://wordpress.org/plugins/svg-support/)
- [Relevanssi](https://www.relevanssi.com/user-manual/)
- [Disable Comments](https://wordpress.org/plugins/disable-comments/)
- [WP Activity Log](https://wordpress.org/plugins/wp-security-audit-log/)
- [WP Migrate](https://deliciousbrains.com/wp-migrate-db-pro/)
- [WP 2FA](https://wordpress.org/plugins/wp-2fa/)
- [WebToffee's CookieYes GDPR/CCPA/Cookie Consent plugin](https://wordpress.org/plugins/cookie-law-info/)
- [MainWP Child](https://wordpress.org/plugins/mainwp-child/)
- [WP All Import Pro](http://www.wpallimport.com/)
- [Post Type Switcher](https://wordpress.org/plugins/post-type-switcher/)
- [Mailgun](https://wordpress.org/plugins/mailgun/)
- [Members](https://wordpress.org/plugins/members/)

## Parent Theme

The parent theme is the core theme that contains WebMechanix's customizations for performance and optimization.

Additional customizations can be made in the parent theme, but it is not recommended. Documentation should be provided
as to why the customization is being made in the parent theme. Pulling optimizations back into the parent repository
should be considered.

### Structure

| Folder   | Description                           |
|----------|---------------------------------------|
| acf-json | ACF local JSON                        |
| blocks   | Gutenberg ACF blocks                  |
| dist     | compiled assets                       |
| includes | PHP includes                          |
| lib      | PHP functions, filters, actions, CPTs |
| src      | source assets                         |

### Blocks

#### ACF Blocks

ACF blocks are located in the `blocks` folder. Each block has its own folder add the following files:

- `BLOCK-NAME.php` - block template
- `src/BLOCK-NAME.scss` - block styles
- `src/BLOCK-NAME.js` - block javascript
- `BLOCK-NAME.json` - block settings
- `acf-json/BLOCK-NAME.json` - block settings

### Concepts

#### ACF

ACF is used to create custom fields for posts, pages, and theme options. ACF is used to create option fields for the
blocks.

#### Bootstrap

Bootstrap is used for the grid system, utility classes, and select few component functionality.

A `branding.scss` file is created to override the Bootstrap variables. This allows us to customize the Bootstrap
variables without modifying the Bootstrap source files.

Within assets SCSS files we will use CSS variables when possible. This allows us to override the Bootstrap variables
without modifying the Bootstrap source files. Typically, we will not use the SCSS variables, as these can change between
projects and version.

## Child Theme

The child theme is the theme that contains the customizations for the project. This is where all the design and project
specific assets should be placed.

### Structure

The child theme will have fewer files and folder, but if you need to add new functionality follow the same pattern as
the parent theme.

> Any file that is included through the locate_template function in the parent theme, typically in `functions.php` can
> be overridden in the child theme by placing it in the same location within the child theme.

### Blocks

> #### ACF Blocks
> You can override any assets of the parent blocks by including them in a same named folder and file. The child theme
> file will override the parent theme file.

## Changelog

### 1.0.1 - 2024-05-01

- Updated organization of files and folders
- Better split of parent and child theme
- Added new features
  - Blocks ...
  - Block level `functions.php` file
- Fixed bugs
  - Fixed bug with ...

### 1.0.0 - 2023-10-01

- Initial release

