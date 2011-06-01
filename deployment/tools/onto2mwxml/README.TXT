
The tool "onto2mwxml" is not shipped with the Deployment Framework but with the TripleStore Connector (TSC).
Please download the TSC and put a link to it named 'tsc' in this directory here (ie. deployment/tools/onto2mwxml).

However, the DF is fully functional without this tool except that ontology import won't work. Note that
you need Java 1.6 to be installed when using the "onto2mwxml" tool.

#
# Installation on Windows 7 / Vista / Server 2008
#

Run the following command in deployment/tools/onto2mwxml:

   mklink /D tsc <tsc-dir>

#
# Installation on Windows XP 
#

 Windows XP users can use the tool linkd.exe from Microsoft to create directory links.
 
 (1) Download: [http://www.microsoft.com/downloads/en/details.aspx?FamilyID=9d467a69-57ff-4ae7-96ee-b18c4790cffd&displaylang=en]
 
 (2) Then run the following command in deployment/tools/onto2mwxml:
 
   linkd tsc <tsc-dir>

#
# Installation on Linux
#

Run the following commands in deployment/tools/onto2mwxml:

   chmod +x onto2mwxml.sh
   ln -s <tsc-dir> tsc

   
#
# Test installation
#

Try to run onto2mwxml without any parameters. 

On Windows: 
        onto2mwxml.bat
On Linux: 
        ./onto2mwxml.sh

You should see something like this:

    Onto2MWXML version 0.1 Patchlevel 0
    
    Missing input file. Use -i [file]