# Atelier Integration

When [Atelier](https://github.com/blackpig-creatif/atelier) is present in your application, Réplique automatically registers a **Comments (Réplique)** block. No manual wiring is required — the service provider detects Atelier at boot time via `class_exists`.

## The Block

The block is named `replique-comments-block` and appears in Atelier's block picker as **Comments (Réplique)**. It renders the `<livewire:replique::comments>` component with configuration set by the editor in the Atelier panel.

The commentable model is resolved automatically from the block's own `blockable` polymorphic relationship — whichever model the block is attached to becomes the commentable. There is no need to configure a target model manually.

## Schema Fields

Editors can configure the following display and behaviour options per block instance:

| Field | Type | Description |
|---|---|---|
| `title` | Text | Section heading (default: `'Comments'`) |
| `allow_anonymous` | Toggle | Allow unauthenticated submissions |
| `require_auth` | Toggle | Force login before commenting |
| `nesting_depth` | Select | `0` = flat, `1` = one reply level, `null` = unlimited |
| `text_mode` | Select | `plain`, `escaped_html`, or `markdown` |
| `reaction_types` | Checkbox list | Which reaction types to enable |
| `require_approval` | Toggle | Hold new comments for moderation |

All fields fall back to their `config/replique.php` defaults if not configured.

## Requirements

The model the block is attached to must use the `HasComments` trait. If the resolved `blockable` does not use `HasComments`, the Livewire component will fail when it tries to query comments. Decorate the model with `#[Commentable]` and `HasComments` as normal:

```php
#[Commentable(label: 'Page')]
class Page extends Model
{
    use HasComments;
}
```
