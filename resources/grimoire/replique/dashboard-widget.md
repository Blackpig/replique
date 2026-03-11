---
title: Dashboard Widget
order: 7
icon: heroicon-o-chart-bar-square
---

# Dashboard Widget

The Réplique dashboard widget gives you an at-a-glance view of comment activity on your site's dashboard. It updates automatically every 60 seconds so you always have a current picture without needing to refresh.

> 📸 **Stuart:** Add a screenshot of the dashboard widget showing the three stat cards

## What it shows

The widget displays three counts:

| Stat | Description |
|---|---|
| **Pending Review** | Comments that have been submitted but not yet moderated — these need your attention |
| **Approved Today** | Comments approved in the last 24 hours |
| **Spam Caught** | Comments automatically flagged as spam (by the honeypot) or manually marked as spam |

## Enabling the widget

The widget is not shown by default. Your developer needs to enable it when registering the plugin:

```php
RepliquePlugin::make()->withDashboardWidget()
```

If you do not see the widget on your dashboard, ask your developer to enable it.

## Acting on pending comments

Clicking the **Pending Review** stat takes you directly to the Comments table with the **Pending only** filter pre-applied, so you can work through the queue immediately.

> **Tip:** If you regularly need to moderate comments, keep the dashboard open in a browser tab — the widget polls every 60 seconds and the Pending Review count will update as new comments arrive.
