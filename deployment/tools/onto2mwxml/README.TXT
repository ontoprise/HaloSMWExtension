
The tool "onto2mwxml" is not shipped with the Deployment Framework but with the TripleStore Connector (TSC).
Please download the TSC and put a link to it named 'tsc' in this directory here (ie. deployment/tools/onto2mwxml).

However, the DF is fully functional without this tool except that ontology import won't work. Note that
you need Java 1.6 to be installed when using the "onto2mwxml" tool.

#
# Installation on Windows 7 / Vista / Server 2008 / XP
#

   (1) Set TSCDIR containing the full path to the TSC directory in onto2mwxml.bat:
   
        SET TSCDIR=c:\Programme\Ontoprise\TSC           (example)

        Note: Paths containing whitespaces MUST be quoted!
#
# Installation on Linux
#

Run the following commands in deployment/tools/onto2mwxml:

   (1) chmod +x onto2mwxml.sh
   (2) Set TSCDIR containing the full path to the TSC directory in onto2mwxml.sh:
            
            TSCDIR=/usr/local/share/Ontoprise/tsc       (example)

   
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