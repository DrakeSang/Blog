$(document).ready(function () {
    $('.categories_navigation li a').on('click', function (e) {
        e.preventDefault();

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
    });

    $('.add_to_favourites').on('click', function(e) {
        e.preventDefault();

        let activeUrl = $(this).attr('href');

        $.ajax({
            url: activeUrl,
            success: function (response) {
                let typeOfResponse = typeof(response);

                if(typeOfResponse === "string") {
                    location.href = '\\login';
                } else {
                    if(response["message"] === "alreadyAdded") {
                        alert('This article was already added to your favourites!');
                    }else {
                        alert('The article was successfully added to your favourites!');
                    }

                }
            }
        })
    });
});