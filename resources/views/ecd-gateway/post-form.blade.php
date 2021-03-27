<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>{{ __('ecd-ipg::messages.redirecting_page_title') }}</title>
    <script type="text/javascript">
        window.addEventListener("load", function(){
            document.getElementById('form').submit();
        });
    </script>
</head>
<body>
<div>{{ __('ecd-ipg::messages.waiting_message_to_payment') }}</div>
<form action="{{ $data->getURL() }}" method="POST" id="form">
    @foreach($data->getFormData() as $fieldName => $value)
        <input name="{{ $fieldName }}" type="hidden" value="{{ $value }}"/>
    @endforeach
</form>
</body>
</html>
