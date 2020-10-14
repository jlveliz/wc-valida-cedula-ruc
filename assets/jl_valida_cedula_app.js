var showHideDocuments = (e) => {
  let typeSelected = e.currentTarget.value;
  let inputRuc = document.getElementById("jl-field-ruc_field");
  let inputCedula = document.getElementById("jl-field-cedula_field");
  let inputPasaporte = document.getElementById("jl-field-pasaporte_field");

  if (typeSelected == "cedula") {
    inputCedula.style = "display:block";
    inputCedula.setAttribute("required", "required");

    inputRuc.style = "display:none";
    inputRuc.value = "";
    inputRuc.removeAttribute("required");

    inputPasaporte.style = "display:none";
    inputPasaporte.value = "";
    inputPasaporte.removeAttribute("required");
  } else if (typeSelected == "ruc") {
    inputRuc.style = "display:block";
    inputRuc.setAttribute("required", "required");

    inputCedula.value = "";
    inputCedula.removeAttribute("required");
    inputCedula.style = "display:none";

    inputPasaporte.style = "display:none";
    inputPasaporte.value = "";
    inputPasaporte.removeAttribute("required");
  } else {
    inputPasaporte.style = "display:block";
    inputPasaporte.setAttribute("required", "required");

    inputCedula.value = "";
    inputCedula.removeAttribute("required");
    inputCedula.style = "display:none";

    inputRuc.style = "display:none";
    inputRuc.value = "";
    inputRuc.removeAttribute("required");
  }
};

var documentType = document.getElementById("document_type");

documentType.addEventListener("change", showHideDocuments);
