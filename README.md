# Esteban Payret — WordPress portfolio

The WordPress version of my portfolio, alongside the [Next.js version](https://www.estebanpayret.com/) and the [HubSpot version](https://247631214.hs-sites-na2.com/esteban-payret-tech-lead-people-manager-hubspot-version).

**▶ Live demo:** [Open in WordPress Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/payretesteban/esteban-wp-portfolio/main/blueprint.json)
(Runs entirely in your browser, takes a few seconds to boot. You're logged in as admin, so you can explore `/wp-admin` too. Changes reset when you close the tab.)

## What it shows

| Area | Where |
|---|---|
| Custom **block theme** (Full Site Editing) with `theme.json` v3 design tokens: palette, fluid type, spacing scale, element & block styles | `theme/esteban-portfolio/theme.json` |
| HTML **templates** and **template parts** (front page, page, project archive, single project, 404) | `theme/esteban-portfolio/templates`, `parts` |
| PHP-registered **block patterns** (hero, about, experience timeline, featured projects, contact) | `theme/esteban-portfolio/patterns` |
| Custom **block style** + `theme.json` style variation (“Card” group) | `functions.php`, `theme.json` |
| Custom **block** with no build step: the animated `<EP/>` logo (block.json, server render, editor controls) | `theme/esteban-portfolio/blocks/logo` |
| Global **style variation** “Daylight” (light theme matching estebanpayret.com), switchable in Appearance → Editor → Styles | `theme/esteban-portfolio/styles/daylight.json` |
| Self-hosted **Geist / Geist Mono** variable fonts via `theme.json` `fontFace` | `theme/esteban-portfolio/assets/fonts` |
| Sticky blurred header, hero glow, timeline, card hover and **CSS scroll-driven reveal animations** (no JS, respects reduced motion) | `theme/esteban-portfolio/assets/css/theme.css` |
| **Content model in a plugin**: `project` post type, `tech` taxonomy, REST-exposed post meta | `plugins/esteban-portfolio-core` |
| **Block Bindings API**: project role and URL rendered from post meta in the single-project template | `templates/single-project.html` |
| **Query Loop** grids pulling the custom post type | `patterns/featured-projects.php`, `templates/archive-project.html` |
| Reproducible demo via a **Playground Blueprint** that installs from this repo | `blueprint.json` |

## Repo layout

```
blueprint.json                 # Playground recipe → the public demo link
content/content.xml            # WXR export of demo content (projects, terms, meta)
plugins/esteban-portfolio-core # content model (CPT, taxonomy, meta)
theme/esteban-portfolio        # the block theme
set-github-user.sh             # rename the GitHub user/repo everywhere if needed
```

## Publishing changes

Commit and push to `main` (GitHub Desktop or `git push`). The Live demo link always builds from the latest `main`, so there's nothing else to deploy.

## Local development with LocalWP

1. Install [LocalWP](https://localwp.com/) and create a site (e.g. `esteban-wp`).
2. Symlink the theme and plugin into it so you edit the repo directly
   (Local → right-click site → *Reveal in Finder* to find the path):
   ```bash
   SITE=~/"Local Sites/esteban-wp/app/public/wp-content"
   ln -s "$PWD/theme/esteban-portfolio" "$SITE/themes/esteban-portfolio"
   ln -s "$PWD/plugins/esteban-portfolio-core" "$SITE/plugins/esteban-portfolio-core"
   ```
3. In wp-admin: activate the plugin and theme, set **Settings → Permalinks → Post name**, then **Tools → Import → WordPress** and import `content/content.xml`.
4. Edit content in WordPress. When you’re happy, **Tools → Export → Projects**, save over `content/content.xml`, commit and push — the demo link picks it up automatically.

> Design changes made in the Site Editor are stored in the database, not in files. To keep them, copy them back into the theme (or use the [Create Block Theme](https://wordpress.org/plugins/create-block-theme/) plugin → “Save changes to theme”) before committing.

## Images

Playground can’t see files on your computer. Put images in `theme/esteban-portfolio/assets/` and reference them from patterns with `get_theme_file_uri()`, or reference them in content by their `raw.githubusercontent.com` URL.
