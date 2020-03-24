<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <style>
        h1 {
            padding: 3rem 2rem;
            text-align: center;
            background: #0097fb;
            color: #fff;
        }

        .lead {
            font-size: 1.25rem;
            font-weight: 300;
        }

        .pb-2,
        .py-2 {
            padding-bottom: 0.5rem !important;
        }

        .pt-2,
        .py-2 {
            padding-top: 0.5rem !important;
        }

        p {
            margin-top: 0px;
            margin-bottom: 1rem;
        }

        *,
        ::after,
        ::before {
            box-sizing: border-box;
        }

        user agent stylesheet p {
            display: block;
            margin-block-start: 1em;
            margin-block-end: 1em;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
        }

        .text-light {
            color: rgb(248, 249, 250) !important;
        }

        .text-center {
            text-align: center !important;
        }

        .mb-auto,
        .my-auto {
            margin-bottom: auto !important;
        }

        .mt-auto,
        .my-auto {
            margin-top: auto !important;
        }

        .px-4 {
            padding-left: 1.5rem !important;
        }

        .px-4 {
            padding-right: 1.5rem !important;
        }

        .pb-2,
        .py-2 {
            padding-bottom: 0.5rem !important;
        }

        .pt-2,
        .py-2 {
            padding-top: 0.5rem !important;
        }

        .bg-primary {
            background-color: rgb(0, 123, 255) !important;
        }

        .py-5 {
            padding-bottom: 3rem !important;
        }

        .pt-5,
        .py-5 {
            padding-top: 3rem !important;
        }

        .bg-dark {
            background-color: rgb(52, 58, 64) !important;
        }

        .token {
            margin: 2rem 4rem;
            color: #fff;
            font-weight: bolder;
        }

        footer {
            margin-top: 2rem;
            display: block;
            color: #fff;
        }

        ::-moz-selection {
            /* Code for Firefox */
            color: red;
            background: yellow;
        }

        ::selection {
            color: red;
            background: yellow;
        }
    </style>
</head>

<body id="page-top">
    <header class="bg-primary text-white">
        <div class="container text-center p-4 tit">
            <h1 class="p-4">Reset your password</h1>
        </div>
    </header>

    <section id="about">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto py-4">
                    <h2>Hi {{$username}}, </h2>
                    <p class="lead">You have requested to change your password.</p>
                    <p class="py-2">
                        Copy the link below to reset your password. If you did'nt request for a new password, you can safely delete the email .
                    </p>
                    <span class="bg-primary px-4 py-2 text-light text-center token ">{{$token}}</span>
                </div>
            </div>
        </div>
    </section>
    <!-- Footer -->
    <footer class="py-5 bg-dark">
        <div class="container">
            <p class="m-0 text-center text-white">Copyright © cashingames.com {{$current_year}}</p>
        </div>
        <!-- /.container -->
    </footer>
</body>

</html>