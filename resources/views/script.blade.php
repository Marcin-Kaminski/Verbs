<div style="font-size: 40px; position: absolute;">@php echo $verbs[$verbForm]; @endphp</div>
<script type="text/javascript">
    function updateInputs() {
        let PLInput = document.getElementById('field-verbinpolish-683e9089f1462d325e967468b760767a65fd6b07');
        let infinitiveInput = document.getElementById('field-verbininfinitive-12737b55e02130b0f3496f2467281c183ed82f98');
        let pastSimpleInput = document.getElementById('field-verbinpastsimple-79e230d5e1fc5d70bb89e9b435f2d2822c7c5abf');
        let pastParticipleInput = document.getElementById('field-verbinpastparticiple-fa4f3a0b1e1506f6e98e768136fb33c70b02c122');
        let verbForm = @php echo json_encode($verbForm); @endphp;

        switch (verbForm) {
            case 'verb_in_polish':
                PLInput.disabled = true;
                break;
            case 'verb_in_infinitive':
                infinitiveInput.disabled = true;
                break;
            case 'verb_in_past_simple':
                pastSimpleInput.disabled = true;
                break;
            case 'verb_in_past_participle':
                pastParticipleInput.disabled = true;
                break;
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        updateInputs();
    })
    document.addEventListener("click", function() {
        updateInputs();
    })
</script>
