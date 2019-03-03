$(document).ready(function () {
    $('.categories_navigation li a').on('click', function (e) {
        $('.categories_navigation li.checked').removeClass('checked');
        $(this).parent().addClass('checked');

        let targetPage = $(this).prop('href');
        let categoryChoice = $(this).text();

        $('.article-body').addClass('loading');

        $.ajax({
            type: "post",
            url: targetPage,
            data: {
                categoryChoice: categoryChoice
            },
            success: function (html) {
                let htmlToLoad = $(`<div>${html}</div>`).find('.main').html();

                $('.main').html(htmlToLoad);

                $('.article-body').removeClass('loading');
            },
            error: function() {
                alert('the page has error......');
            },
            complete: function() {
                $('.article-body').removeClass('loading');
            }
        });

        e.preventDefault();
    })
});