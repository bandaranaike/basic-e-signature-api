<!DOCTYPE html>
<html>
<head>
    <style>
        .signature {
            position: absolute;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
        }
        .footer-text {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            background-color: green;
            color: white;
            padding: 5px;
        }
    </style>
</head>
<body>
{!! $pdf->stream() !!}
<div class="signature">
    <img src="{{ $imageDataUri }}" alt="Signature" height="100">
</div>
<div class="footer-text">
    Signed by {{ $userName }} on {{ $currentDate }}
</div>
</body>
</html>
