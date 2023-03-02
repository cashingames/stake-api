<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>

<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; background-color: #ffffff; color: #74787e; height: 100%; hyphens: auto; line-height: 1.4; margin: 0; -moz-hyphens: auto; -ms-word-break: break-all; width: 100% !important; -webkit-hyphens: auto; -webkit-text-size-adjust: none; word-break: break-word;">
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
            
        }

        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }

        h1 {
            text-align: center;
            padding-top: 2rem;
        }
        
        h3 {
            text-align: center;
            padding-top: 2rem;
        }
        
    </style>

    <h1>Daily Report ({{$todaysDate}})</h1>
    
    <table>
        <tr>
            <th>Net Platform Gain</th>
            <th>Total Fundings</th>
            <th>Total Withdrawals</th>
            <th>Total Staked Amount</th>
            <th>Total Amount Won</th>
        </tr>
        <tr>
            <td>{{$data['netProfitAndLoss']}}</td>
            <td>{{$data['totalFundedAmount']}}</td>
            <td>{{$data['totalWithdrawals']}}</td>
            <td>{{$data['totalStakedAmount']}}</td>
            <td>{{$data['totalAmountWon']}}</td>
        </tr>
      
    </table>
</body>

</html>