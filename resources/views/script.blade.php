<div style="font-size: 40px; position: absolute;">@php echo $verbs[$verbForm]; @endphp</div> </br>

<div style="position: absolute; margin-top: 35px;">Liczba błędów przy tym czasowniku: @php echo $verbs['errors'] @endphp</div>
<div style="position: absolute; margin-top: 55px">Liczba błędów w tej sesji: @php echo $allErrors @endphp</div>
<script type="text/javascript">
    function updateInputs() {
        // jeśli czemuś skrypt nie działa, porównaj z id, jakie pokazuje, zbadaj na elemencie, możliwe, że trzeba by to
        // kiedyś zmienić na querySelector
        let PLInput = document.getElementById('field-verb-in-polish-d04b5940eb588ab793105c63901706987d90ccb5');
        let infinitiveInput = document.getElementById('field-verb-in-infinitive-19891befe29bc883815c7695b2c83a4bc760e664');
        let pastSimpleInput = document.getElementById('field-verb-in-past-simple-0235746de23c7e896d8693688e2b5caffe876dc3');
        let pastParticipleInput = document.getElementById('field-verb-in-past-participle-a98735e03abdfd99064e231fa414f974fd1bdea7');
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
