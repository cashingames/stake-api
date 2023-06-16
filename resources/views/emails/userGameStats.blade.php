<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

</head>

<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; box-sizing: border-box; background-color: #ffffff; color: #74787e; height: 100%; hyphens: auto; line-height: 1.4; margin: 0; -moz-hyphens: auto; -ms-word-break: break-all; width: 100% !important; -webkit-hyphens: auto; -webkit-text-size-adjust: none; word-break: break-word;">
    <style>
        td:nth-child(even) {
      background-color: lightgray;
    }
        @media only screen and (max-width: 600px) {
            table {
        width: 100%;
      }
      td {
        display: block;
        width: 100%;
      }
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
    </style>
    <div style="text-align: center; margin: 0 auto; padding: 2rem; background-color: #15397D">
        <h5 style="font-size: 1rem; margin: 0; color: #fff">Hello {{$data['username']}}</h5> 
        <h5 style="font-size: 1rem; margin-top: 0; color: #fff;" >This is a bi-weekly update on your gaming experience on GameArk.</h5>
        <table style="border-collapse: collapse; width: 80%; margin: 0 auto; border-radius: 10px;">
            <tr>
                <td style="border: 1px solid #0D2859; padding: 10px; font-size: 0.8rem;  color: #fff">Number of games played</td>
                <td style="border: 1px solid #0D2859; padding: 10px; font-size: 0.8rem;  color: #000;  font-weight: 600 ">{{$data['gamePlayed']}}</td>
            </tr>
             <tr>
                <td style="border: 1px solid #0D2859; padding: 10px; font-size: 0.8rem;  color: #fff">Category played the most</td>
                <td style="border: 1px solid #0D2859; padding: 10px; font-size: 0.8rem;  color: #000;  font-weight: 600"> {{ $data['category']['name'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #0D2859; padding: 10px; font-size: 0.8rem;  color: #fff">Win Rate</td>
                <td style="border: 1px solid #0D2859; padding: 10px; font-size: 0.8rem; color: #000;  font-weight: 600">{{$data['win_rate']}}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #0D2859; padding: 10px; font-size: 0.8rem;  color: #fff">Correct answers percentage</td>
                <td style="border: 1px solid #0D2859; padding: 10px; font-size: 0.8rem;  color: #000;  font-weight: 600">{{$data['correctCountAverage']}}%</td>
            </tr>
            <tr>
                <td style="border: 1px solid #0D2859; padding: 10px; font-size: 0.8rem;  color: #fff">Available Boosts</td>
                <td style="border: 1px solid #0D2859; padding: 10px; font-size: 0.8rem;  color: #000;  font-weight: 600">{{$data['availableBoost']}}</td>
            </tr>
        </table>
        <div style="margin-top: 2rem; display: flex; justify-content: space-around">
            <a href="https://web.facebook.com/The.Gameark" style="margin-right: 1rem; color: #fff"><i class="fab fa-facebook-f"></i></a>
            <a href="https://twitter.com/GameArk_" style="margin-right: 1rem;  color: #fff"><i class="fab fa-twitter"></i></a>
            <a href="https://www.instagram.com/_gameark_/" style=" color: #fff"><i class="fab fa-instagram"></i></a>
        </div>
        <p  style="font-size: 0.9rem; color: #fff"> You're doing great! But it can always be better!💪💪</p>
      </div>
</body>
</html>
