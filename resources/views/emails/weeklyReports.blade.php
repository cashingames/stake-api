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

        .reports,
        .stakers {
            border-collapse: collapse;
            font-size: 0.9em;
            font-family: sans-serif;
            min-width: 400px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        }

        thead tr {
            color: #ffffff;
            text-align: left;
        }

        td,
        th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        thead th {
            width: 25%;
        }

        th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
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

        div {
            padding: 20px;
            background-color: #e6ffff;
            overflow-x: auto;
        }
    </style>

    <h1>Weekly Report</h1>

    <div>
        <table class="reports">
            <tr>
                <th>Net Platform Gain</th>
                <td>{{$data['netProfit']}}</td>
            </tr>
            <tr>
                <th>Total Fundings</th>
                <td>{{$data['totalFundedAmount']}}</td>
            </tr>
            <tr>
                <th>Total Withdrawals</th>
                <td>{{$data['totalWithdrawals']}}</td>
            </tr>
            <tr>
                <th>Total Staked Amount</th>
                <td>{{$data['totalStakedamount']}}</td>
            </tr>
            <tr>
                <th>Total Amount Won</th>
                <td>{{$data['totalAmountWon']}}</td>
            </tr>
            <tr>
                <th>Completed Staking Sessions</th>
                <td>{{$data['completedStakingSessionsCount']}}</td>
            </tr>

        </table>
    </div>
    <h3>Stakers</h3>
    <div>
        <table class="stakers">
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Amount Staked</th>
                <th>Amount Won</th>
            </tr>
            @foreach($data['stakers'] as $key=>$data)
            <tr>
                <td>{{$key + 1}}</td>
                <td>{{$data->username}}</td>
                <td>{{$data->amount_staked}}</td>
                <td>{{$data->amount_won}}</td>
            </tr>
            @endforeach
        </table>
    </div>
</body>

</html>