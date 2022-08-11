# sd2html WLM Service Definition XML Formatter

This tool converts a Workload Manager (WLM) Service Definition from XML to HTML, adding linking to aid navigation.

(It is not intended that the HTML could ever be turned back into XML.)

It uses PHP 8 (as a minimum level) either in a webserver or on the command line.

## In A Webserver

To run in a webserver you would need to install PHP and the sd2html.php file in the webserver. localhost would do fine, as would one on the network.

To invoke it point the web client at the PHP code like so:

	http://localhost/<where-php-runs>/sd2html.php?sds=http://localhost/<where-the-xml-is>/<wlm-service-definition>.xml

replacing the values in angle brackets as appropriate.

## On The Command Line

To run from the command line you would need PHP installed.

Then you uses stdin and stdout like so

	php sd2html < <my-xml.xml> > <my-html.html>

Once you've converted to HTML you use your browser to view the HTML version.

