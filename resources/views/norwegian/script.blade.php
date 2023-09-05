<div style="font-size: 40px; position: absolute;">@php echo $words[$wordForm]; @endphp</div> </br>

<div style="position: absolute; margin-top: 35px;">Liczba błędów przy tym czasowniku: @php echo $words['errors'] @endphp</div>
<div style="position: absolute; margin-top: 55px">Liczba błędów w tej sesji: @php echo $allErrors @endphp</div>
<script type="text/javascript">
    function updateInputs() {
        // jeśli czemuś skrypt nie działa, porównaj z id, jakie pokazuje, zbadaj na elemencie, możliwe, że trzeba by to
        // kiedyś zmienić na querySelector
        let PLInput = document.getElementById('field-word-in-polish-2738054aa970b82b0dff6800364a1e8b6e3986c1');
        let NorwegianInput = document.getElementById('field-word-in-norwegian-de811f7f3c5450de011dcb91d508e94bb731496a');
        let wordForm = @php echo json_encode($wordForm); @endphp;
        console.log(wordForm)
        switch (wordForm) {
            case 'word_in_polish':
                PLInput.disabled = true;
                break;
            case 'word_in_norwegian':
                NorwegianInput.disabled = true;
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
