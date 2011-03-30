rem checks, but does not execute an import  (--dr)
php IAI_CommandLine.php --a="Pablo Picasso" --ew --tmpl --img --dr

rem imports an article from english wikipedia with templates and images
php IAI_CommandLine.php --a="Pablo Picasso" --ew --tmpl --img

rem imports an article only if it is not already there
php IAI_CommandLine.php --a="Pablo Picasso" --ew --tmpl --img --se
php IAI_CommandLine.php --a="Henri_Matisse" --ew --tmpl --img --se

rem imports an image
php IAI_CommandLine.php --i="File:VanGogh-starry night ballance1.jpg" --ew

rem imports from file (ultrapedia usecase)
php IAI_CommandLine.php --af="test_Adam_Opel.nt" --ew --tmpl --img

rem imports from simple file from wikipedia
php IAI_CommandLine.php --af="import.txt" --api="http://en.wikipedia.org/w/" --tmpl --img --aregex="^(.*?)$"