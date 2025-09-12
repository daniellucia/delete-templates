jQuery(document).ready(function ($) {

    $('.theme').each(function () {
        const $theme = $(this);
        const slug = $theme.data('slug');
        const $actions = $theme.find('.theme-actions');

        if (slug && RW_DELETE_THEMES.links[slug]) {
            const $deleteBtn = $('<a/>', {
                text: RW_DELETE_THEMES.button_text,
                class: 'button delete-theme-btn',
                href: '#'
            });

            $deleteBtn.attr('data-href', RW_DELETE_THEMES.links[slug].replace(/&amp;/g, '&'));

            $deleteBtn.on('click', function (e) {
                e.preventDefault();
                const url = $(this).attr('data-href');
                const themeElement = $(this).closest('.theme');

                $.confirm({
                    title: RW_DELETE_THEMES.confirm_title,
                    content: RW_DELETE_THEMES.confirmation_text,
                    buttons: {
                        cancel: {
                            text: RW_DELETE_THEMES.cancel_text
                        },

                        confirm: {
                            text: RW_DELETE_THEMES.confirm_text,
                            btnClass: 'btn-red',
                            action: function () {
                                window.location.href = url;
                                themeElement.addClass('deleting');
                            }
                        }

                    }
                });
            });

            if (!$actions.parents('.theme').hasClass('active')) {
                $actions.append($deleteBtn);
            }
        }

    });
});
