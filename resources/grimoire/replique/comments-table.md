---
title: The Comments Table
order: 1
icon: heroicon-o-table-cells
---

# The Comments Table

The Comments table shows every comment across all content types on your site. By default it is grouped by record so you can see at a glance which piece of content has activity, but you can work with the full flat list too.

> 📸 **Stuart:** Add a screenshot of the full Comments table with the default grouping visible, showing a few grouped rows

## Columns

| Column | Description |
|---|---|
| **On** | The content type and record ID the comment belongs to (e.g. `Post #12`) |
| **Reply to** | Shows `↩ #ID` if this is a reply; hover over it to see a preview of the parent comment |
| **From** | The commenter's name. Shows the authenticated user's name, or the anonymous name/email for guest comments |
| **Comment** | A truncated excerpt of the comment text. Hover to see the full text |
| **Status** | A coloured badge: **Pending** (amber), **Approved** (green), **Rejected** (red), **Spam** (orange) |
| **Reactions** | A summary of reaction counts per type (e.g. `like: 4 · dislike: 1`) |
| **IP** | The submitter's IP address — hidden by default, toggle it on from the column picker |
| **Posted** | How long ago the comment was submitted, with the full date and time on hover |

## Grouping

Comments are grouped by record by default — all comments on `Post #12` collapse into one group header. Click the group header to expand or collapse it.

To remove the grouping and see a flat list, click the **Group** button in the table toolbar and deselect the current grouping.

> 📸 **Stuart:** Add a screenshot showing a collapsed group header and an expanded one side by side, or just the group header with the record label

## Sorting

Click any column header to sort by that column. Click again to reverse the direction. The default sort is newest first.

## Filtering

Click the **Filters** button to narrow down the table:

| Filter | What it does |
|---|---|
| **Status** | Show only comments with one or more selected statuses |
| **Model type** | Show only comments attached to a specific content type |
| **Pending only** | Toggle to show only pending comments (or only non-pending) |
| **Date range** | Filter by submission date — set a from date, a to date, or both |
| **Trashed** | Show soft-deleted comments, or show only deleted ones |

> 📸 **Stuart:** Add a screenshot of the filters panel open with a couple of filters active

## Searching

The search box in the top-right searches across the comment text, the commenter's email address, name, and IP address simultaneously.

## The column picker

Click the **Columns** toggle (the columns icon in the toolbar) to show or hide the IP address column, which is hidden by default.
