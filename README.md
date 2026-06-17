# Brocode_AdminhtmlCacheEviction

Adds **quick-action links** directly to the Magento 2 admin cache-invalidated notification bar — the yellow warning that appears after saving a configuration value or any other action that invalidates caches. Admins can act on the spot without navigating to Cache Management.

---

## Why

The default notification reads:

> One or more of the Cache Types are invalidated: config, full_page. Please go to Cache Management and refresh cache types.

That means two clicks and a page load before anything happens. This module appends the action links inline so the fix is one click from wherever the admin already is.

---

## What it adds

Three links are appended to the notification (each only rendered when the current admin user has the required permission):

| Link | Action | ACL resource |
|---|---|---|
| Refresh Invalidated Caches | Cleans only the currently-invalidated cache types | `Magento_Backend::refresh_cache_type` |
| Flush Magento Cache | Flushes all Magento cache frontends | `Magento_Backend::flush_magento_cache` |
| Flush Cache Storage | Flushes the underlying cache storage backend | `Magento_Backend::flush_cache_storage` |

The "Refresh Invalidated Caches" action is a dedicated GET controller that reads the live invalidated type list and cleans only those — no manual type selection needed.

---

## Install

Drop the module into `app/code/Brocode/AdminhtmlCacheEviction`, then:

```bash
bin/magento module:enable Brocode_AdminhtmlCacheEviction
bin/magento setup:upgrade
bin/magento setup:di:compile   # production / compiled mode only
bin/magento cache:flush
```

Or via Composer once the package is published:

```bash
composer require brocode/module-adminhtml-cache-eviction
bin/magento module:enable Brocode_AdminhtmlCacheEviction
bin/magento setup:upgrade
bin/magento cache:flush
```

---

## Verify

1. Log in to the admin panel.
2. Go to **Stores → Configuration**, change any setting, and save.
3. The yellow notification bar should now read something like:

   > One or more of the Cache Types are invalidated: config. Please go to Cache Management and refresh cache types. **Quick actions:** Refresh Invalidated Caches | Flush Magento Cache | Flush Cache Storage

4. Click **Refresh Invalidated Caches** — you land on the Cache Management page with a success message and the notification is gone.

To test permission gating, assign the admin user a role that lacks `Magento_Backend::flush_magento_cache` and confirm the "Flush Magento Cache" link is absent while the others remain.

---

## Notes

- **CSP-safe.** Links are plain `<a href>` elements — no inline `onclick` or `javascript:` URIs. Compatible with Magento 2.4.7+ default Content Security Policy.
- **ACL-gated.** Each link uses the same ACL resource as the corresponding core controller, so restricted admin roles never see actions they cannot perform.
- **Area-scoped plugin.** The plugin is declared in `etc/adminhtml/di.xml`, so it only applies in the admin area.

---

## File tree

```
Brocode/AdminhtmlCacheEviction/
├── registration.php
├── composer.json
├── README.md
├── etc/
│   ├── module.xml
│   └── adminhtml/
│       ├── di.xml
│       └── routes.xml
├── Plugin/
│   └── CacheOutdatedPlugin.php
└── Controller/
    └── Adminhtml/
        └── Cache/
            └── Refresh.php
```
