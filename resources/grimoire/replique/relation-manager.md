---
title: Relation Manager
order: 6
icon: heroicon-o-rectangle-stack
---

# Relation Manager

If your developer has added the Réplique relation manager to a content resource — such as Posts, Products, or Pages — you can manage comments for a specific record directly from that record's edit screen, without going to the main Comments table.

## Where to find it

Open any content record that has comments enabled (for example, edit a blog post). Look for a **Comments** tab at the top of the edit page.

> 📸 **Stuart:** Add a screenshot of a content resource edit page showing the Comments tab in the tab bar

## What the relation manager shows

The Comments tab shows a table of all comments attached to the record you are currently editing. The columns are the same as the main Comments table, with the addition of a **Depth** badge that shows whether a comment is a top-level root comment or a reply.

> 📸 **Stuart:** Add a screenshot of the relation manager table with a mix of root comments and replies, showing the Depth badge

## Available actions

Each row has the same moderation actions as the main Comments table:

- **Reply** — post a reply to this comment
- **Approve / Reject / Mark as spam** — moderate the comment
- **Edit** — edit the text, change the status, or toggle pinned; the parent thread is shown above the form for context
- **Restore** — restore a soft-deleted comment
- **Delete** — soft-delete the comment

Bulk actions are not available in the relation manager view.

## Difference from the main Comments table

The relation manager only shows comments for the single record you have open. It is useful when you want to stay in context while reviewing feedback on a specific piece of content, rather than switching to the full Comments section and filtering down.
