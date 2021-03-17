<!DOCTYPE html>
<html lang="en">
<head>
    <title>Redirecting to gateway</title>
    <script type="text/javascript">
        window.addEventListener("load", function(){
            document.getElementById('form').submit();
        });
    </script>
</head>
<body>
<div>Please wait a while, we will redirect you to the payment gateway ...</div>
<form action="{{ $data->getURL() }}" method="POST" id="form">
    @foreach($data->getFormData() as $fieldName => $value)
        <input name="{{ $fieldName }}" type="hidden" value="{{ $value }}"/>
    @endforeach
</form>
</body>
</html>