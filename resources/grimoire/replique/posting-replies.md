---
title: Posting Replies
order: 4
icon: heroicon-o-chat-bubble-left-ellipsis
---

# Posting Replies

You can post a reply to any comment directly from the admin panel without visiting the frontend. This is useful for official responses, answering questions, or following up on moderated comments.

## How to post a reply

1. Find the comment you want to reply to in the Comments table
2. Open the row action menu (the three-dot icon)
3. Click **Reply**
4. Type your reply in the **Your reply** field
5. Select the **Text mode** — this controls how the text is processed and stored
6. Click **Post reply**

> 📸 **Stuart:** Add a screenshot of the Reply modal open with some text in the reply field

## How the reply appears

The reply is attributed to the admin account that is currently logged in. It is automatically set to **Approved** and appears on the frontend immediately, regardless of whether the site is configured to hold new comments for moderation.

The reply is nested under the parent comment in the thread, at the next depth level.

## Text modes

| Mode | Behaviour |
|---|---|
| **Plain** | All HTML tags are stripped. The text is stored and displayed as plain text |
| **Escaped HTML** | Tags are stripped and special characters are HTML-encoded. Safe for output anywhere |
| **Markdown** | Text is converted from Markdown to HTML. Raw HTML in the input is stripped |

The default text mode is set in the site configuration. Choose **Markdown** if you want to use formatting like **bold**, _italics_, or links in your reply.
