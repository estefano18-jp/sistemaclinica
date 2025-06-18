
function AlertaSweetAlert(type, title, text, url){

    switch (type) {
        case "error":

        if (url == "") {

            Swal.fire({
                icon: "error",
                title: "Error",
                text: text,
                confirmButtonColor: '#3085d6',
            });

        }else{

            Swal.fire({

                icon: "error",
                title: "Error",
                text: text,
                confirmButtonColor: '#3085d6',
            }).then((result) =>{

                if (result.value) {

                    window.open(url, "_top");

                }

            });

        }

        break;

        case "success":

        if (url == "") {

            Swal.fire({

                icon: "success",
                title: "Correcto",
                text: text,
                confirmButtonColor: '#3085d6',
            });

        }else{

            Swal.fire({

                icon: "success",
                title: "Correcto",
                text: text,
                confirmButtonColor: '#3085d6',
            }).then((result) =>{

                if (result.value) {

                    window.open(url, "_top");

                }

            });

        }

        break;

        case "registro":

        if (url == "") {

            Swal.fire({

                icon: "success",
                title: "Registro exitoso",
                text: text,
                confirmButtonColor: '#3085d6',
            });

        }else{

            Swal.fire({

                icon: "success",
                title: "Correcto",
                text: text,
                confirmButtonColor: '#3085d6',
            }).then((result) =>{

                if (result.value) {

                    window.open(url, "_top");

                }

            });

        }

        break;

        case "verificacion":

        if (url == "") {

            Swal.fire({

                icon: "success",
                title: title,
                text: text,
                confirmButtonColor: '#3085d6',
            });

        }else{

            Swal.fire({

                icon: "success",
                title: title,
                text: text,
                confirmButtonColor: '#3085d6',
            }).then((result) =>{

                if (result.value) {

                    window.open(url, "_top");

                }

            });

        }

        break;

        case "warning":

        if (url == "") {

            Swal.fire({

                icon: "warning",
                title: title,
                text: text,
                confirmButtonColor: '#3085d6',
            });

        }else{

            Swal.fire({

                icon: "warning",
                title: title,
                text: text,
                confirmButtonColor: '#3085d6',
            }).then((result) =>{

                if (result.value) {

                    window.open(url, "_top");

                }

            });

        }

        break;

        case "loading":

            Swal.fire({
                allowOutsideClick: false,
                icon: 'info',
                title: title,
                text: text
            });
            Swal.showLoading()

        break;

        case "closeLoading":

            Swal.close();

        break;
        
        case "confirm":

            return new Promise(resolve=>{

                Swal.fire({
                    text: text,
                    icon: 'warning',
                    title: title,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'No',
                    confirmButtonText: 'Si, continuar!'
                }).then(function(result){

                    resolve(result.value);

                });

            });

        break;

    }

}