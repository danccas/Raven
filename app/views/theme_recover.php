
<!DOCTYPE html>
<html lang=en>

<head>
  <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-97227851-2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-97227851-2');
</script>
</head>

<body>

<style type="text/css">
  body{
    background-color: #f2f2f2;
  }

  .code-card{
    padding-left:10px;
    padding-right:10px;
    padding-top:5px;
    padding-bottom:5px;
    border-radius: 10px;
    background-color: #f2f2f2;
    border: 1px solid #BEBEBE;
  }
</style>
<style type="text/css">
  @media (min-width: 768px) {
    html {
      font-size: 16px;
    }
  }

  .search-bar{
    max-width: 500px;
    width: 100%;
  }

  form{
    width: 100%;
  }

</style>

<!-- Body -->
<style type="text/css">
  .main{
    min-height: 100vh;
    height: 100%;
  }
</style>
<div class="main">
  

<style type="text/css">
  .form-signin {
    width: 100%;
    max-width: 330px;
    padding: 15px;
    margin: auto;
  }
  .form-signin .checkbox {
    font-weight: 400;
  }
  .form-signin .form-control {
    position: relative;
    box-sizing: border-box;
    height: auto;
    padding: 10px;
    font-size: 16px;
  }
  .form-signin .form-control:focus {
    z-index: 2;
  }
  .form-signin input[type="email"] {
    margin-bottom: 10px;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 0;
  }
  .h3{
    text-align: center;
  }
</style>


<form id="id_password_reset_form" method="POST" class="form-signin">
  <h1 class="h3 mb-3 font-weight-normal">Recuperar Clave</h1>
    <input name="email" class="form-control" placeholder="Email" type="email" id="id_email" required="true" >
  <button id="id_submit_btn" type="button" class="btn btn-lg btn-primary btn-block" >Enviar correo eléctronico</button>  
</form>

<script type="text/javascript">

  var submitButton = document.getElementById('id_submit_btn');
  var form = document.getElementById('id_password_reset_form');

  submitButton.addEventListener('click', function (e) {

//      AndroidTextListener.onLoading(true)

      e.preventDefault();
      var email = document.getElementById("id_email").value
      
      var xhr = new XMLHttpRequest();
      xhr.open('GET', '/api/account/check_if_account_exists/?email=' + email);
      xhr.onload = function() {
          if (xhr.status === 200) {
              var response = JSON.parse(xhr.responseText)
              if(response.response == email){
                console.log(email + " is a valid email!")
                AndroidTextListener.onSuccess(email)
                form.submit()
              }
              else{
                console.log(email + " is NOT valid email!")
                AndroidTextListener.onError("That email doesn't exist on our servers.")
              }
          }
          else {
              console.log(xhr.status)
          }
          AndroidTextListener.onLoading(false)
      };
      xhr.send();

  });

</script>



</div>
<!-- End Body -->

<style type="text/css">
  .footer{
    min-height: 100px;
  }
</style>

<!-- Footer -->

<div class="d-flex flex-row align-items-center footer bg-white shadow-lg mt-4">
  <p class="m-auto">GymFit 2020/p>
</div>
<!-- End Footer -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
