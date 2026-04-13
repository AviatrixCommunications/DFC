/* global jQuery, tmSort */
(function ($) {
  $(function () {
    var $list = $('#the-list');

    if (!$list.length) {
      return;
    }

    $list.sortable({
      items: 'tr',
      axis: 'y',
      placeholder: 'tm-sort-placeholder',
      cursor: 'grabbing',
      opacity: 0.8,
      update: function () {
        var order = [];

        $list.children('tr').each(function () {
          order.push($(this).attr('id').replace('post-', ''));
        });

        $.post(tmSort.ajaxUrl, {
          action: 'sort_team_members',
          nonce: tmSort.nonce,
          order: order,
        });
      },
    });
  });
})(jQuery);
