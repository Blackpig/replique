---
title: IP Blocking
order: 3
icon: heroicon-o-no-symbol
---

# IP Blocking

If a visitor repeatedly submits spam, abusive comments, or otherwise causes problems, you can block their IP address. Any future comment submission from a blocked IP receives a generic error and the comment is never stored.

## Blocking an IP from a comment

1. Find a comment from the offending visitor in the Comments table
2. Open the row action menu (the three-dot icon)
3. Click **Block IP**
4. The IP address is pre-filled — confirm it is correct
5. Optionally enter a **Reason** for the block (for your own records)
6. Click **Block IP** to confirm

> 📸 **Stuart:** Add a screenshot of the Block IP modal open, showing the pre-filled IP field and the reason textarea

The block takes effect immediately. The visitor receives a generic "unable to post comment" error on their next submission attempt — the message gives no indication that their IP is specifically blocked.

## What happens to blocked submissions

Blocked IPs are checked before any other validation runs. If the IP is on the blocklist, the form returns an error and nothing is stored. The visitor is not informed that their IP is blocked — only that they cannot post at this time.

## Viewing and managing blocks

> 📸 **Stuart:** Add a screenshot of the Blocked IPs resource table if it exists, or note that IP management is currently only available via the comment row action

Blocks are stored permanently until manually removed. There is currently no expiry mechanism — a block remains in place indefinitely.
