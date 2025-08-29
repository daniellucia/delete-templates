jQuery(document).ready(function($) {
    
    $('.theme').each(function() {
        const $theme   = $(this);
        const slug     = $theme.data('slug');
        const $actions = $theme.find('.theme-actions');

        if (slug && RW_DELETE_THEMES.links[slug]) {
            const $deleteBtn = $('<a/>', {
                text: RW_DELETE_THEMES.button_text,
                class: 'button rw-delete-theme-btn',
                href: RW_DELETE_THEMES.links[slug]
            });

            $deleteBtn.on('click', function(e) {
                if ($(this).parents('.theme').hasClass('active')) {
                    alert(RW_DELETE_THEMES.alert_text);
                    e.preventDefault();

                    return;
                }

                if (!confirm(RW_DELETE_THEMES.confirmation_text)) {
                    e.preventDefault();
                }
            });

            $actions.append($deleteBtn);
        }
        
    });
});
