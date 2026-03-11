@if ($commentable)
    @php
        $rawDepth = $block->get('nesting_depth'); // null when not saved to EAV
        if ($rawDepth === null) {
            $depth = config('replique.nesting_depth'); // honour the site config
        } else {
            $depth = $rawDepth === 'unlimited' ? null : (int) $rawDepth;
        }
    @endphp

    <livewire:replique::comments
        :model="$commentable"
        :title="$block->get('title', 'Comments')"
        :allow-anonymous="(bool) $block->get('allow_anonymous', true)"
        :require-auth="(bool) $block->get('require_auth', false)"
        :depth="$depth"
        :text-mode="$block->get('text_mode', 'escaped_html')"
        :reaction-types="$block->get('reaction_types', [])"
        :require-approval="(bool) $block->get('require_approval', false)"
    />
@endif
