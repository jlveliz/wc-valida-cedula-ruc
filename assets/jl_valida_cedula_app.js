
var showHideDocuments =  (e) => {
    let typeSelected = e.currentTarget.value;
    let inputRuc = document.getElementById('jl-field-ruc_field');
    let inputCedula = document.getElementById('jl-field-cedula_field');
    
    if(typeSelected == 'cedula') {
        inputCedula.style = 'display:block';
        inputCedula.setAttribute('required','required')
        
        inputRuc.style = 'display:none';
        inputRuc.value = '';
        inputRuc.removeAttribute('required');
    } else {
        inputRuc.style = 'display:block';
        inputRuc.setAttribute('required','required');

        inputCedula.value = '';
        inputCedula.removeAttribute('required')
        inputCedula.style = 'display:none';
    }
    

}

var documentType = document.getElementById('document_type');

documentType.addEventListener('change', showHideDocuments )