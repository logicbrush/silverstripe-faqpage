jQuery(document).ready(function() {
   
    /** FAQ filtering **/
    jQuery('.faq-filter-form').on('submit', function(e) {
        e.preventDefault();
    });
    jQuery('.faq-filter').on('keyup.faq-filter, change.faq-filter', function() {
        var searchVal = jQuery(this).val().toLowerCase();
        var faqTableOfContents = jQuery('.faq-table-of-contents');
        var filterItems = faqTableOfContents.find('li');
        var faqHeadings = jQuery('.faq-section-heading');

        filterItems.removeClass('hidden');
        faqHeadings.removeClass('hidden');

        if (searchVal != '') {
            filterItems.each(function() {
                var itemContent = jQuery(this).text().toLowerCase();
                if (itemContent.indexOf(searchVal) < 0) {
                    jQuery(this).addClass('hidden');
                }
            });
        }

        faqTableOfContents.each(function() {
            if ($(this).find('> li.hidden').length === $(this).find('> li').length) {
                jQuery(this).prev('.faq-section-heading').addClass('hidden');
            }
        });
    }).trigger('change.faq-filter');

    /** Accordian behavior */
    jQuery('.faq-table-of-contents>li>a').each(function() {
        $(this).on('click', function() {
            $(this).next().toggleClass('hidden');
            return false;
        });
    });

});
