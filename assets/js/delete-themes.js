jQuery(document).ready(function($) {
    
    $('.theme').each(function() {
        const $theme   = $(this);
        const slug     = $theme.data('slug');
        const $actions = $theme.find('.theme-actions');

        if (slug && RW_DELETE_THEMES.links[slug]) {
            const $deleteBtn = $('<a/>', {
                text: RW_DELETE_THEMES.button_text,
                class: 'button delete-theme-btn',
                href: RW_DELETE_THEMES.links[slug].replace(/&amp;/g, '&')
            });

            $deleteBtn.on('click', function(e) {
                if (!confirm(RW_DELETE_THEMES.confirmation_text)) {
                    e.preventDefault();
                }
            });

            if (!$actions.parents('.theme').hasClass('active')) {
                $actions.append($deleteBtn);
            }
        }
        
    });
});
