define(['jquery'], function (jq) {

    $(document).ready(function () {
        let num;
        let total = $(".Price_txt").text();
        $("#ti-plus").click(function () {
            num = parseInt($(".txt_Num").text()) + 1;
            $(".txt_Num").text(num);

            $(".Price_txt").text(parseInt(total) * num);
        });

        $("#ti-minus").click(function () {
            if (parseInt($(".txt_Num").text()) > 1) {
                num = parseInt($(".txt_Num").text()) - 1;
                $(".txt_Num").text(num);

                $(".Price_txt").text(parseInt(total) * num);
            }
        });


        $("#bt_less_3").click(function(){
            $("#card2").hide();
            $("#card1").hide();
            $("#Comment_Card").hide();
        });

        $("#bt_all").click(function(){
            $("#card2").show();
            $("#card1").show();
            $("#Comment_Card").show();

        });

        $("#bt_over_3").click(function(){
            $("#card2").show();
            $("#card1").show();
            $("#Comment_Card").show();

        });

        $("#bt_over_4").click(function(){
            $("#card2").show();
            $("#card1").show();
            $("#Comment_Card").show();

        });

    });
    // SetDate
    const todayDate = new Date().toISOString().slice(0, 10);
    document.getElementById("datePicker").min = todayDate;
})