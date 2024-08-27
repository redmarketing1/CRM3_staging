<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #FFF !important;
        }

        th.sitelinks a {
            color: #333;
            padding: 2px 10px !important;
            background: #f9f9f9;
            display: inline-block;
            margin-bottom: 5px;
        }

        .domainlink a {
            background: #EE232A;
            color: #FFF !important;
            padding: 1px 10px;
            margin-bottom: 3px;
            display: inline-block;
            font-weight: bold !important;
            margin-right: 30px;
            line-height: 33px;
        }

        .footerlogo {
            width: 190px;
        }

        body {
            font-family: Arial !important;
            font-size: 16px !important;
        }

        .maintext {
            font-family: Arial !important;
            font-size: 16px !important;
            padding: 10px;
        }

        a {
            color: #EE232A;
            font-weight: bold;
            text-decoration: none;
        }

        th,
        td {
            text-align: left;
            font-weight: normal
        }

        .footerbox a {
            color: #333;
            font-weight: normal;
        }

        .footerbox a.domainlink {
            color: #EE232A;
        }

        .footerbox .social a:last-child .subleft a,
        .footersub2 a,
        .social a {
            color: #FFF !important;
        }

        .disclaimer a {
            font-weight: normal;
            color: inherit;
            border-bottom: 1px dotted;
        }

        p {
            line-height: 22px;
        }

        div {
            box-sizing: border-box !important;
        }

        ol,
        ul {
            margin: 20px 10px 20px 0;
            padding: 10px 0 10px 40px;
            list-style: none;
            line-height: 20px
        }

        ul {
            line-height: 25px;
            padding: 10px 0 10px 38px;
        }

        ul li:before {
            content: "✱";
            padding-right: 12px;
            font-weight: bold;
            font-size: 20px;
            background: #EE232A;
            width: 28px;
            display: inline-block;
            margin-bottom: 2px;
            color: #FFF;
            padding: 0 !important;
            text-align: center;
            font-size: 13px;
            max-height: 25px;
            position: relative;
            right: 10px;
            margin-left: -28px;
            padding: 2px 7px;
        }

        ul ul li:before {
            background: #e1e1e1;
            padding-right: 12px;
            font-weight: bold;
            font-size: 11px;
            color: #FFF;
            width: 23px;
            height: 23px;
        }

        ul ul ul li:before {
            font-weight: bold;
            font-size: 15px;
            color: #777;
            background: none;
            font-style: normal;
        }

        ul ul ul ul li:before {
            content: "-";
            font-size: 15px;
            color: #333;
        }

        .footerbox ul li:before {
            display: none;
            padding: 0;
            margin: 0;
        }

        ol ol,
        ul ul {
            font-weight: 400;
            margin-top: 0px;
            padding-top: 5px;
        }

        ol ol ol,
        ul ul ul {
            border: none;
            padding: 0 0 0 40px;
            font-style: italic;
        }

        ol ol ol ol,
        ul ul ul ul {
            font-style: normal;
        }

        ol li {
            list-style-type: none;
            list-style-image: none;
            counter-increment: list;
            position: relative;
        }

        ol ol li {
            counter-increment: list2;
        }

        ol ol ol li {
            counter-increment: list3;
        }

        ol ol ol ol li {
            counter-increment: list4;
        }

        ol ol ol ol ol li {
            counter-increment: list5;
        }

        ol li:before {
            background: #EE232A;
            font-weight: bold;
            content: counter(list) ".";
            position: relative;
            right: 10px;
            margin-left: -30px;
            padding: 2px 7px;
            font-size: 18px;
            margin-bottom: 2px;
            display: inline-block;
            color: #FFF;
            border-radius: 0px;
        }

        ol ol li:before {
            content: counter(list) "." counter(list2);
            background: #e1e1e1;
            color: #333;
            font-size: 13px;
        }

        ol ol ol li:before {
            content: counter(list) "." counter(list2) "." counter(list3);
            background: #f5f5f5;
            color: #555;
        }

        ol ol ol ol li:before {
            content: counter(list) "." counter(list2) "." counter(list3) "." counter(list4);
        }

        ol ol ol ol ol li:before {
            content: counter(list) "." counter(list2) "." counter(list3) "." counter(list4) "." counter(list5);
        }

        .maintext table {
            width: 100%;
            border: 10px solid #f1f1f1;
            border-collapse: collapse !important;
            box-sizing: border-box;
        }

        .maintext th {
            border: 1px solid #e1e1e1;
            padding: 10px 15px;
        }

        .maintext th {
            border: 2px solid #f1f1f1;
            padding: 8px 10px;
        }

        .footerbox th {
            border: none;
            vertical-align: bottom;
            padding: 15px 15px;
        }

        .footerlogo {
            transition: all 0.3s ease-in-out
        }

        .mainframe {
            max-width: 1000px;
        }

        #mainframe.mainframe .maintext ul li:before {
            content: "-" !important;
        }

        /********************** MOBILE CSS *****************************/

        @media (max-width: 680px) {
            .domainlink a {
                display: inline-block !important;
                float: none;
                text-align: center;
                width: 200px;
                margin: 0 auto 20px;
            }

            .maintext {
                font-size: 18px !important;
            }

            table,
            tbody,
            tr,
            td,
            th {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box;
                font-size: 18px;
            }

            th {
                text-align: center !important;
                padding: 10px 5px !important;
            }

            .footerlogo {
                float: none !important;
                margin: -95px 0 0px -75px !important
            }

            div {
                width: 100% !important;
            }

            .tablewrapper {
                text-align: center;
                margin-top: 50px !important;
            }

            a.logo-link {
                margin-top: 25px !important;
                margin-left: -10px !important;
            }
        }

        /********************** END MOBILE *****************************/
    </style>
</head>

<body style="font-family: Arial; font-size: 16px; color:#222;">
    <div class="mainframe" class="mainframe">
        <div class="maintext">
            {!! $content !!}
            <p></p>
            <p>Beste Grüße</p>
            <p>Team Neuwest<br>
                <em>Kundenbetreuung</em><br>
                <strong>Neuwest Bauunternehmen</strong>
            </p>
        </div>
        <div class="outerwrapper" style="padding:10px;">
            <div class="tablewrapper" style="border-top: 5px solid #EE232A; margin:20px 0 0;  background: #FFF; color:#333; padding: 10px 25px;box-sizing: border-box;
    box-shadow: 0px 2px 6px 1px rgba(0,0,0,0.3);">
                <table width="100%" border="0" bgcolor="#FFF" class="footerbox" style="background: #FFF; box-sizing: border-box; border-collapse: collapse; box-sizing: border-box;color:#333; line-height: 25px;">
                    <tbody>
                        <tr style="border-bottom:2px solid #f5f5f5;">
                            <th width="auto"><a class="logo-link" href="http://www.neu-west.com"><img width="190" class="footerlogo" style="width: 190px;" src="http://neu-west.com/wp-content/uploads/2022/09/Neuwest-Logo-Symbol-parallel-norm.svg" alt="Neuwest" title="Neuwest" /></a>
                            </th>
                            <th width="auto"><span class="subname" style="font-weight:bold; font-size:18px;">Team Neuwest</span><br />+49(0)30 236 10 252</th>
                            <th>
                                <div class="domainlink">
                                    <a target="_blank" href="http://www.neu-west.com" mce_href="http://www.neu-west.com">www.neu-west.com</a>
                                </div>
                            </th>
                        </tr>
                        <tr style="border-bottom:2px solid #f5f5f5;">
                            <th width="auto">Neuwest Office:<br>
                                Jobs & Karriere:</th>
                            <th width="auto">+49(0)30 232 55 7470<br>
                                +49 (0)30 232 55 7477</th>
                            <th>kontakt@neu-west.com<br>
                                jobs@neu-west.com</th>
                        </tr>
                        <tr>
                            <th class="sitelinks" colspan="3">
                                <a target="_blank" href="https://neu-west.com/sanierung/">Sanierung</a>
                                <a target="_blank" href="https://neu-west.com/ausbau/">Ausbau</a>
                                <a target="_blank" href="https://neu-west.com/baugutachter-bausachverstaendiger/">TÜV Baugutachten</a>
                                <a target="_blank" href="https://neu-west.com/rohbau/">Rohbau</a>
                                <a target="_blank" href="https://neu-west.com/waermedaemmung/">Wärmedämmung</a>
                                <a target="_blank" href="https://neu-west.com/trockenbau/">Trockenbau</a>
                                <a target="_blank" href="https://neu-west.com/abbruch/">Abbruch</a>
                                <a target="_blank" href="https://neu-west.com/baustoffe/">Infos zu Baustoffen</a>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="disclaimer" style="clear: both;list-style: none;padding: 5px;font-size: 11px;line-height: 11px;color: #ccc;margin: 10px 0;font-style: italic;">Vertraulichkeitshinweis: Jedwede unbefugte Verwendung oder Weitergabe der Informationen dieser Nachricht ist untersagt. // Confidentiality Note: Any unauthorized use or transfer of the information contained in this message is prohibited. </div>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <style>
            /********************** CSS *****************************/
            body {
                background: #FFF !important;
            }

            th.sitelinks a {
                color: #333;
                padding: 2px 10px !important;
                background: #f9f9f9;
                display: inline-block;
                margin-bottom: 5px;
            }

            .domainlink a {
                background: #EE232A;
                color: #FFF !important;
                padding: 1px 10px;
                margin-bottom: 3px;
                display: inline-block;
                font-weight: bold !important;
                margin-right: 30px;
                line-height: 33px;
            }

            .footerlogo {
                width: 190px;
            }

            body {
                font-family: Arial !important;
                font-size: 16px !important;
            }

            .maintext {
                font-family: Arial !important;
                font-size: 16px !important;
                padding: 10px;
            }

            a {
                color: #EE232A;
                font-weight: bold;
                text-decoration: none;
            }

            th,
            td {
                text-align: left;
                font-weight: normal
            }

            .footerbox a {
                color: #333;
                font-weight: normal;
            }

            .footerbox a.domainlink {
                color: #EE232A;
            }

            .footerbox .social a:last-child .subleft a,
            .footersub2 a,
            .social a {
                color: #FFF !important;
            }

            .disclaimer a {
                font-weight: normal;
                color: inherit;
                border-bottom: 1px dotted;
            }

            p {
                line-height: 22px;
            }

            div {
                box-sizing: border-box !important;
            }

            ol,
            ul {
                margin: 20px 10px 20px 0;
                padding: 10px 0 10px 40px;
                list-style: none;
                line-height: 20px
            }

            ul {
                line-height: 25px;
                padding: 10px 0 10px 38px;
            }

            ul li:before {
                content: "✱";
                padding-right: 12px;
                font-weight: bold;
                font-size: 20px;
                background: #EE232A;
                width: 28px;
                display: inline-block;
                margin-bottom: 2px;
                color: #FFF;
                padding: 0 !important;
                text-align: center;
                font-size: 13px;
                max-height: 25px;
                position: relative;
                right: 10px;
                margin-left: -28px;
                padding: 2px 7px;
            }

            ul ul li:before {
                background: #e1e1e1;
                padding-right: 12px;
                font-weight: bold;
                font-size: 11px;
                color: #FFF;
                width: 23px;
                height: 23px;
            }

            ul ul ul li:before {
                font-weight: bold;
                font-size: 15px;
                color: #777;
                background: none;
                font-style: normal;
            }

            ul ul ul ul li:before {
                content: "-";
                font-size: 15px;
                color: #333;
            }

            .footerbox ul li:before {
                display: none;
                padding: 0;
                margin: 0;
            }

            ol ol,
            ul ul {
                font-weight: 400;
                margin-top: 0px;
                padding-top: 5px;
            }

            ol ol ol,
            ul ul ul {
                border: none;
                padding: 0 0 0 40px;
                font-style: italic;
            }

            ol ol ol ol,
            ul ul ul ul {
                font-style: normal;
            }

            ol li {
                list-style-type: none;
                list-style-image: none;
                counter-increment: list;
                position: relative;
            }

            ol ol li {
                counter-increment: list2;
            }

            ol ol ol li {
                counter-increment: list3;
            }

            ol ol ol ol li {
                counter-increment: list4;
            }

            ol ol ol ol ol li {
                counter-increment: list5;
            }

            ol li:before {
                background: #EE232A;
                font-weight: bold;
                content: counter(list) ".";
                position: relative;
                right: 10px;
                margin-left: -30px;
                padding: 2px 7px;
                font-size: 18px;
                margin-bottom: 2px;
                display: inline-block;
                color: #FFF;
                border-radius: 0px;
            }

            ol ol li:before {
                content: counter(list) "." counter(list2);
                background: #e1e1e1;
                color: #333;
                font-size: 13px;
            }

            ol ol ol li:before {
                content: counter(list) "." counter(list2) "." counter(list3);
                background: #f5f5f5;
                color: #555;
            }

            ol ol ol ol li:before {
                content: counter(list) "." counter(list2) "." counter(list3) "." counter(list4);
            }

            ol ol ol ol ol li:before {
                content: counter(list) "." counter(list2) "." counter(list3) "." counter(list4) "." counter(list5);
            }

            .maintext table {
                width: 100%;
                border: 10px solid #f1f1f1;
                border-collapse: collapse !important;
                box-sizing: border-box;
            }

            .maintext th {
                border: 1px solid #e1e1e1;
                padding: 10px 15px;
            }

            .maintext th {
                border: 2px solid #f1f1f1;
                padding: 8px 10px;
            }

            .footerbox th {
                border: none;
                vertical-align: bottom;
                padding: 15px 15px;
            }

            .footerlogo {
                transition: all 0.3s ease-in-out
            }

            .mainframe {
                max-width: 1000px;
            }

            .maintext ul li:before {
                content: "=" !important;
            }

            /********************** MOBILE CSS *****************************/
            @media (max-width: 680px) {
                .domainlink a {
                    display: inline-block !important;
                    float: none;
                    text-align: center;
                    width: 200px;
                    margin: 0 auto 20px;
                }

                .maintext {
                    font-size: 18px !important;
                }

                table,
                tbody,
                tr,
                td,
                th {
                    display: block !important;
                    width: 100% !important;
                    box-sizing: border-box;
                    font-size: 18px;
                }

                th {
                    text-align: center !important;
                    padding: 10px 5px !important;
                }

                .footerlogo {
                    float: none !important;
                    margin: -95px 0 0px -75px !important
                }

                div {
                    width: 100% !important;
                }

                .tablewrapper {
                    text-align: center;
                    margin-top: 50px !important;
                }

                a.logo-link {
                    margin-top: 25px !important;
                    margin-left: -10px !important;
                }
            }

            /********************** END MOBILE *****************************/
        </style>
</body>

</html>