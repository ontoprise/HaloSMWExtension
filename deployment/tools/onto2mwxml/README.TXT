
The tool "onto2mwxml" is not shipped with the Deployment Framework but with the TripleStore Connector (TSC).
Please download the TSC and put a link to it named 'tsc' in this directory here.

However, the DF is fully functional without this tool except that ontology import won't work.

#
# Installation on Windows 7 / Vista / Server 2008
#

Run the following command in this directory:

   mklink /D tsc <tsc-dir>

 Note: Windows XP users can use the tool linkd.exe from Microsoft to create directory links.

#
# Installation on Linux
#

Run the following commands in this directory:

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