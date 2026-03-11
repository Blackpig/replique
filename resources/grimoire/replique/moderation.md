---
title: Moderation
order: 2
icon: heroicon-o-shield-check
---

# Moderation

Each comment in the table has a set of moderation actions available from the row action menu on the right. You can also act on multiple comments at once using the bulk actions toolbar.

> 📸 **Stuart:** Add a screenshot of the row action dropdown open on a comment, showing all the available actions

## Comment statuses

A comment moves through a simple lifecycle:

| Status | Meaning |
|---|---|
| **Pending** | Submitted but not yet reviewed. Not visible on the frontend if approval is required |
| **Approved** | Visible to visitors on the frontend |
| **Rejected** | Hidden from the frontend. The commenter is not notified |
| **Spam** | Flagged as unwanted. Hidden from the frontend |

Comments caught by the honeypot are automatically set to **Spam** on submission — you never see them arrive as Pending.

## Row actions

Open the action menu (the three-dot icon at the end of any row) to see the available actions for that comment.

### Approve

Sets the comment to **Approved** and makes it visible on the frontend immediately. The approval timestamp and approving admin are recorded.

Only available when the comment is not already approved and has not been deleted.

### Reject

Sets the comment to **Rejected**. Requires confirmation before the action runs.

Only available when the comment is not already rejected and has not been deleted.

### Mark as Spam

Sets the comment to **Spam**. Requires confirmation. Useful for catching submissions that bypassed the automatic honeypot.

Only available when the comment is not already marked as spam and has not been deleted.

### View

Opens a read-only modal showing the full comment text, processed output, moderation history, attribution details, and reaction counts. If this comment is a reply, the full parent thread is shown above it so you have context.

> 📸 **Stuart:** Add a screenshot of the View modal open on a reply, showing the ancestry thread above the comment

### Edit

Opens an edit modal where you can:

- **Edit the comment text** — the sanitised output is regenerated automatically on save
- **Change the text mode** — Plain, Escaped HTML, or Markdown
- **Change the status** — directly set the moderation status
- **Toggle Pinned** — pin this comment to the top of its thread

If this comment is a reply, the parent thread is shown above the form for context.

> 📸 **Stuart:** Add a screenshot of the Edit modal open, showing the ancestry section and the form fields below

### Reply

Post a reply to this comment directly from the admin panel. The reply is attributed to your currently logged-in admin account and is automatically set to **Approved**.

See [Posting Replies](posting-replies) for full details.

### Block IP

Block the IP address associated with this comment. Any future comment submissions from that IP will be rejected.

See [IP Blocking](ip-blocking) for full details.

### Restore

Appears only on soft-deleted comments. Restores the comment to its previous status.

### Delete

Soft-deletes the comment. It remains in the database and can be restored. Use the **Trashed** filter to find deleted comments.

## Bulk actions

Select multiple comments using the checkboxes on the left of each row, then use the bulk action toolbar that appears at the bottom of the table.

> 📸 **Stuart:** Add a screenshot of the bulk actions toolbar with a few comments selected

| Bulk action | What it does |
|---|---|
| **Approve selected** | Approves all selected comments in one go. Requires confirmation |
| **Reject selected** | Rejects all selected comments. Requires confirmation |
| **Mark as spam** | Marks all selected comments as spam. Requires confirmation |
| **Restore selected** | Restores all selected soft-deleted comments |
| **Delete selected** | Soft-deletes all selected comments |

> **Tip:** Use the **Pending only** filter combined with **Select all** and **Approve selected** to quickly clear a queue of comments waiting for review.
