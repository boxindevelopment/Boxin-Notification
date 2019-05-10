<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Boxin | Invoice</title>

    <style>
    .invoice-box {
        max-width: 800px;
        margin: auto;
        padding: 30px;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        font-size: 16px;
        line-height: 24px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #555;
    }

    .invoice-box table {
        width: 100%;
        line-height: inherit;
        text-align: left;
    }

    .invoice-box table td {
        padding: 5px;
        vertical-align: top;
    }

    .invoice-box table tr.top table td {
        padding-bottom: 20px;
    }

    .invoice-box table tr.top table td.title {
        font-size: 45px;
        line-height: 45px;
        color: #333;
    }

    .invoice-box table tr.information table td {
        padding-bottom: 10px;
    }

    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td {
            width: 100%;
            display: block;
            text-align: center;
        }

        .invoice-box table tr.information table td {
            width: 100%;
            display: block;
            text-align: center;
        }
    }

    /** RTL **/
    .rtl {
        direction: rtl;
        font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    }

    .rtl table {
        text-align: right;
    }

    .rtl table tr td:nth-child(2) {
        text-align: left;
    }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">

            <tr>
              <td colspan="3">
                <hr>
              </td>
            </tr>

            <tr class="information">
                <td style="width: 80px;">
                    Name
                </td>
                <td style="width: 5px;">
                    :
                </td>
                <td>
                  {{ $contact->first_name }} {{ $contact->last_name }}
                </td>
            </tr>
            <tr class="information">
                <td style="width: 80px;">
                    Email
                </td>
                <td style="width: 5px;">
                    :
                </td>
                <td>
                  {{ $contact->email }}
                </td>
            </tr>
            <tr class="information">
                <td style="width: 80px;">
                    Phone
                </td>
                <td style="width: 5px;">
                    :
                </td>
                <td>
                  {{ $contact->phone }}
                </td>
            </tr>
            <tr class="information">
                <td style="width: 80px;">
                    Message
                </td>
                <td style="width: 5px;">
                    :
                </td>
                <td>
                  {{ $contact->message }}
                </td>
            </tr>

            <tr>
              <td colspan="3">
                <hr>
              </td>
            </tr>
        </table>
    </div>
</body>
</html>
