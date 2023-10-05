<html>
   <head>
      <title>Doc Upload with search</title>
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
   </head>
   <body>
      <div class="container">
         <div class="panel panel-primary">
            <div class="panel-heading">
               <h2>Doc Upload with search</h2>
            </div>


            <div class="panel-body">
               @if ($message = Session::get('success'))
                   <div class="alert alert-success alert-block">
                      <button type="button" class="close" data-dismiss="alert">×</button>
                      <strong>{{ $message }}</strong>
                   </div>
               @endif
 
               @if (count($errors) > 0)
               <div class="alert alert-danger">
                  <strong>Whoops!</strong> There were some problems with your input.
                  <ul>
                     @foreach ($errors->all() as $error)
                     <li>{{ $error }}</li>
                     @endforeach
                  </ul>
               </div>
               @endif

               <form>
                    <input type="text" placeholder="Search.."  style="width: -webkit-fill-available;" name="q" id="searchContent">
                </form>
                <span id="contentList" class="context"></span>
 
               <form action="{{ route('store.file') }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="row">
                     <div class="col-md-6">
                        <input type="file" name="file" class="form-control"/>
                     </div>
                     <div class="col-md-6">
                        <button type="submit" class="btn btn-success">Upload File...</button>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </body>


<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
 <script type="text/javascript">
    $('#searchContent').on('keyup',function() {
        var query = $(this).val(); 
        $.ajax({
            url:"{{ route('index') }}",
            type:"GET",
            data:{'query':query},
            success:function (data) {
                $('#contentList').html(data).mark(query);
            }
        })
    });
    $('body').on('click', 'li', function(){
        var value = $(this).text();
        //do what ever you want
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/jquery.mark.es6.js" integrity="sha512-4PUcRoBmsfaiXPoigt+rm4mfuXpvvwfC7dFIhHkwVQGECJzaFDMR8HGTxNDLkwC4DlJq3/EYHL77YXFr34Jmog==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</html>