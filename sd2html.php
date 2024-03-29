<?php
//
// Convert a WLM Service Definition XML File To HTML
//
// MIT License
//
// Copyright (c) 2020 Martin Packer
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
//
//   When   Who                                What
// -------- ---- --------------------------------------------------------------
// 04/30/13 MGLP Extensively reworked to take a query string and do classific-
//               ation groups and classification rules
// 05/01/13 MGLP Added in Resource Groups and Service Policies
// 05/02/13 MGLP Add Workloads, tidied up "types", StorCrit, Region Goals for
//               classification rules
// 05/03/13 MGLP Created $bc variable for a blank cell. Use str_repeat() for
//               repeated blank cells. Added RG to SC. Added Report Classes,
//               Service Parameters, Application Environments, Resources &
//               Scheduling Environments
// 05/05/13 MGLP Renamed to sd2html.php and made classification rules
//               recursive. Created cell,linkify,href HTML helper functions.
// 05/06/13 MGLP Added SYSTEM/SYSSTC hardcoded rows and tidied up with helper
//               functions
// 05/07/13 MGLP Fix broken links and make linkify / href return &nbsp; if
//               second parameter is empty
// 05/14/13 MGLP Added creation and modification dates and userids
// 06/24/13 MGLP Handle lack of description in title, and lack of notes
//               ProcedureName split apart as a qualifier
// 11/11/13 MGLP Fix handling of note elements and smart out Resources
//               and Scheduling environments.
// 02/14/14 MGLP Added Subsystem Collection. Smart out resouce groups.
//               Improved CSS. Handle Average Response Time goals.
//               Suppress CPU Critical column if never specified.
// 07/31/14 MGLP Fixed up nested classification rules and added description.
//               Added Scheduling Environment and Accounting Information to
//               classification rules.
// 08/16/14 MGLP Ensured all the "Modification User" occurrences of "CLW"
//               translated to "Cheryl Watson". Break before descriptions.
//               Refined average/percentile goal time printing.
// 09/22/14 MGLP Fixed bug where 10s prints as 0s.
// 11/12/14 MGLP Add "Transaction Class" and "LU Name" to within "Transaction Group"
//               Decode Resource Group type.
//               Service Policies contains proper Service Class Override information
// 11/21/14 MGLP Break up overrides column names a little better
//               Emphasise Policy Level with h3
// 02/18/15 MGLP Add "Collection Name"
// 09/23/15 MGLP Add "Connection Type"
// 12/15/15 MGLP Add Creation Date and Modification Date tables
//               Get rid of "empty XPath Nodelist" error log messages
// 05/19/16 MGLP Handle Percentage LPAR Share for RG Override type
// 11/10/16 MGLP Support PlanName. Handle case where CreationUser missing
// 02/20/17 MGLP Add support for Package Name and Package Name Group
// 03/27/17 MGLP Cleaned up formatting of Overrides table
// 08/15/17 MGLP Handle I/O Priority Groups. Fixed bugs with classification rules.
//               Resized table text smaller.
// 08/16/17 MGLP Made links between Class Group and Class Rules bjiective.
//               Highlight when Class Group not used in Class Rules.
//               Consolidate names in class groups. Note RCs / SCs not in class rules.
//               Also unused resource groups and schenv resources.
//               Removed lots of "Trying to get property of non-object" error messages
// 11/22/17 MGLP Make long tables scrollable. Add support for z/OS 2.1 matches with a
//               start position. Fix qualifier levels formatting issues. Added shortcut
//               bars with links to workloads and subsystems. Added policy statistics
//               Add namespace at top of HTML, just after source file
// 03/05/18 MGLP Support Reporting Attribute of e.g. Mobile.
//               Uppercase userid in "year" tables
// 07/01/18 MGLP Support SysplexName classification rule. LUName qualifier type
//               Widen resource group description, Type 
// 09/14/18 MGLP Added "smart" HonorPriority column in Service Class table.
// 08/21/19 MGLP Added NUMTCB column in Application Environment table - by parsing parm string
// 10/15/19 MGLP Detect PlanNameGroup
// 03/03/20 MGLP Detect NumberCPsTimes100
// 04/21/20 MGLP Added MIT License text as comment at top
// 06/18/20 MGLP Decode "NumberCPsTimes100" in override
// 06/18/20 MGLP Massage subsystem names in Application Environments table
// 01/05/21 MGLP Decode SystemNameGroup
// 01/26/21 MGLP Decode CPUServiceUnits for Resource Group Override
// 01/27/21 MGLP Added ProdId's LEVELnnn word to the Statistics table
// 03/01/21 MGLP Decode ClientWorkstationName. Support spaces in URL.
//               Support RG Include Specialty Processor Consumption.
//               Also Deactivate Discretionary Goal Management option
// 04/17/21 MGLP Added buttons to create tree in iThoughts of classification rules
// 04/19/21 MGLP Fixed problem in iThoughts CSV with Mobile
// 04/20/21 MGLP Reading in the XML cleans up \r and 0x1A characters
// 04/21/21 MGLP Support SysplexNameGroup in CRs and SysplexName in CGs
//               Recast getting Creation- and Modification User values
// 04/22/21 MGLP Added "Rule " before rule number
//               Added Service Class Period count
// 04/30/21 MGLP Added sysplex names in rules to Statistics table
// 05/25/21 MGLP Handle System Name qualifier in Classification Groups
//               Added system names in rules to Statistics table
//               Added subsystem names in rules to Statistics table
//               Added performance groups in rules to Statistics table
// 09/09/21 MGLP Coloured userids in Creation / Modification Date tables
//          MGLP Handle AccountingInformationGroup in Classification Rules
// 10/27/21 MGLP PHP 8 fixes
// 11/04/21 MGLP More PHP 8 fixes
// 19/07/22 MGLP Handle empty description @ former line 1432
// 23/07/21 MGLP Rework Service Policies table. Another 3 PHP 8 fixes
// 25/07/22 MGLP Indicate whether run from a webserver or command line
// 11/08/22 MGLP Allow to run from command line - using stdin / stdout
// 11/29/22 MGLP Scrollable table max increased from 500 to 850
//               Added statistics on goal types and overrides thereof. Likewise
//               resource groups
// 11/30/22 MGLP Added Service Policies table
//               Fixed Prodid level extraction to be tail of last word

$backgroundColourPalette = ['#FFFFFF','#CCFFCC','#FFDDDD','#CCCCFF','#CCCCCC','#CCFFFF','#F0FFF0','#ADD8E6','red','green','blue','AntiqueWhite','BlueViolet','Aquamarine','DarkSeaGreen','IndianRed'];
$lBackgroundColours = count($backgroundColourPalette);

$foregroundColourPalette = ['#0000C0','#00C000','#00C0C0','#C00000', '#C000C0', '#C0C000',
                            '#0000FF','#00FF00','#00FFFF','#FF0000', '#FF00FF', '#FFFF00',
                            '#000080','#008000','#008080','#800000', '#800080', '#808000'];
$lForegroundColours = count($foregroundColourPalette);
?>
<style type="text/css">
sl
{
  list-style-type: none;
}

pre
{
  font-size: 18px;
}

h1
{
  background: #DDDDFF; 
  border: 1px solid black;
  box-shadow: 10px 10px 5px #888888;
  display: inline-block;
  padding: 8px;
}

h2
{
  background: #DDDDFF; 
  box-shadow: 10px 10px 5px #888888;
  display: inline-block;
  padding: 8px;
}

table
{
  background: #DDDDFF; 
  border-collapse: collapse;
  border: 2px solid black;
  box-shadow: 10px 10px 5px #888888;
 }

th
{
  font-weight: bold;
  background: #BBBBFF;
  padding: 8px;
  font-size: 12px;
}

table.scrollable tbody, table.scrollable thead
{
  display: block;
}

table.scrollable tbody{
  overflow: auto;
  max-height: 850px;
}

table.scrollable th, table.scrollable td{
    min-width: 70px;
    max-width: 70px;
}

td
{
  padding:8px;
  font-size:12px;
}

/* Dialog full screen background */
#dialog-wrap {
  display: none;
  z-index: 9999;
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.5);
}

/* Dialog background */
#dialog-box {
  position: relative;
  background: #fff;
  min-width: 800px;
  max-width: 800px;
  padding: 10px;
  margin: 40vh auto 0 auto;
}

/* Dialog Close Button */
#dialog-close {
  position: absolute;
  top: 0;
  right: 0;
  padding: 5px;
  background: #f54242;
  color: #fff;
  cursor: pointer;
}

/* Dialog text box */
#dialog-text
{
  font-size: 10px;
}
</style>

<!-- Dialog box -->
<div id="dialog-wrap">
  <div id="dialog-box">
    <div id="dialog-close" onclick="myAlert.close()">X</div>
    <h2 id="dialog-head"></h2>
    <p>&nbsp;</p>
    <textarea rows=50 cols=100 id="dialog-text"></textarea>
  </div>
</div>


<script type="text/javascript">

var myAlert = {
    open : function (title, message){
        document.getElementById("dialog-head").innerHTML = title
        document.getElementById("dialog-text").innerHTML = message
        document.getElementById("dialog-wrap").style.display = "block"
    },
    close : function () {
        document.getElementById("dialog-wrap").style.display = "none"
    }
}

function makeTree(subsystemID){
    // Use the passed in node ID to find the node for this subsystem
    subsysRow = document.getElementById(subsystemID).parentNode.parentNode.parentNode
    
    // Get the table body
    tbody = subsysRow.parentNode
    
    // Find the table rows for this subsystem
    subsystemTableRows = []
    foundSubsystem = false
    for (node of tbody.childNodes){
        if(node === subsysRow){
            foundSubsystem = true
            subsystemTableRows.push(node)
            continue
        }
        if(node.nodeName == "#text") continue
        
        if(node.firstChild.childNodes.length == 0) continue
        
        if((node.firstChild.firstChild.nodeName == "STRONG") && foundSubsystem){
            // We've finished picking up table rows
            break
        }
        
        if(foundSubsystem){
            subsystemTableRows.push(node)
        }
    }
    
    // Get subsystem service class and report class
    scColumn = 0
    for (i = 1; i < subsysRow.childNodes.length; i++){
        if(subsysRow.childNodes[i].innerHTML != "&nbsp;"){
            scColumn = i
            break
        }
    }
    subsysSC = subsysRow.childNodes[scColumn].firstChild.innerHTML
    if(subsysSC == undefined){
        subsysSC = ""
    }
    subsysRC = subsysRow.childNodes[scColumn + 1].firstChild.innerHTML
    if(subsysRC == undefined){
        subsysRC = ""
    }
    
    CSVLines = ["colour,level,level0,level1,level2,level3,level4,level5,level6,level7,level8"]

    // Level 0 node for the subsystem
    subsysNode ='"",0,"Subsystem ' + subsystemID.substring(3)

    if (subsysSC !=""){
        subsysNode += "\nSC: " +subsysSC
    }

    if (subsysRC !=""){
        subsysNode += "\nRC: " +subsysRC
    }
    
    subsysNode += '"'

    CSVLines.push(subsysNode)
    
    // Add rules other than default for this subsystem
    for(r = 1; r < subsystemTableRows.length; r++){
        // Find first non-blank cell
        for(c = 1; c < subsystemTableRows[r].childNodes.length; c++){
            cell = subsystemTableRows[r].childNodes[c].innerHTML
            if(cell != "&nbsp;"){
                column = c
                break
            }
        }
        
        level = (column + 2) / 3
        
        // Create cell with rule number and rule type
        cell = "Rule " + r.toString() + "\n" + cell.replaceAll("<br>"," ")
        
        // Add rule value to cell
        ih = subsystemTableRows[r].childNodes[column + 1].innerHTML.replace("<br>"," ").replace("<br/>"," ")
        almostStart = ih.lastIndexOf('">')
        if(almostStart == -1){
            ruleValue = ih
        }else{
            justAfterEnd = ih.lastIndexOf("</a")
            ruleValue = ih.substring(almostStart + 2, justAfterEnd)        
        }
        cell += "\n" +  ruleValue
        
        
        // Add any description to the cell
        description = subsystemTableRows[r].childNodes[column+2].innerHTML
        if(description != "&nbsp"){
            cell += "\n" +  description.replace("<br>"," ").replace("<br/>"," ")
        }
        
        // Add service class to cell
        sclass = subsystemTableRows[r].childNodes[scColumn].firstChild.innerHTML
        if(sclass ==undefined){
            sclass = ""
        }
        cell += "\n\nSC: " +  sclass.replace("<br>"," ").replace("<br/>"," ")
        
        // Add report class to cell
        rclass = subsystemTableRows[r].childNodes[scColumn + 1].firstChild.innerHTML
        if(rclass ==undefined){
            rclass = ""
        }
        cell += "\nRC: " + rclass.replace("<br>"," ").replace("<br/>"," ")
        
        // Perhaps add Storage Critical to cell
        storcrit = subsystemTableRows[r].childNodes[scColumn + 2].innerHTML
        if(storcrit != "No"){
            cell += "\nStorage Critical"
        }
        
        // Perhaps add Reporting Attribute to cell
        reptattr = subsystemTableRows[r].childNodes[scColumn + 4].innerHTML
        if(reptattr != undefined){
            cell += "\n" + reptattr
        }
        
        CSVLines.push('"",' + level.toString() + ',""'.repeat(level) + ',"' + cell +'"')
    }
    
    
    CSV=""
    for(l = 0; l < CSVLines.length; l++){
        CSV += CSVLines[l] + '\n'
    }

    myAlert.open("Select All, Copy & Paste into a CSV file",CSV)
}

</script>

<?php


function cell($s,$align='left',$width=''){
  if($width==""){
    $widthSpec="";
  }else{
    $widthSpec=" style='min-width: ".$width."px; max-width: ".$width."px;'";
  }

  if($align=='left'){
    return "<td$widthSpec>$s</td>";  
  }else{
    return "<td$widthSpec align='$align'>$s</td>";
  }
}

// Common function both a id= and a href=
function linkhref($element,$h,$prefix,$x){
  if($x==""){
    return "&nbsp;";
  }else{
   return "<a $element='$h$prefix"."_"."$x'>$x</a>";
  }
}

// Make target of a link
function linkify($prefix,$x){
  return linkhref("id","",$prefix,$x);
}

// Refer to a link
function href($prefix,$x){
  return linkhref("href","#",$prefix,$x);
}

function blank_cells($n,$align='left',$width=''){
  return str_repeat(cell('&nbsp',$align,$width),$n);
}

// Pump out,recursively, classification rules - $c = node, $l= recursion level
function do_classification_rules($c,$l){
  global $xpath,$bc,$maxClassificationRuleLevel,$seenRCs,$seenSCs;
  $crs=$xpath->query('wlm:ClassificationRules/wlm:ClassificationRule | wlm:ClassificationRule',$c);
  foreach($crs as $cr){
    $qtype=$xpath->query("wlm:QualifierType",$cr)->item(0)->nodeValue;

    $qvalue=$xpath->query("wlm:QualifierValue",$cr)->item(0)->nodeValue;
    switch($qtype){
    case "TransactionNameGroup":
      $qvalueHTML="<span id='CG_USE_$qvalue'><a href='#CG_DEF_$qvalue'>$qvalue</a></span>";
      $qtypeHTML="Transaction<br/>Name Group";
     break;
    case "TransactionName":
      $qtypeHTML="Transaction<br/>Name";
      $qvalueHTML=$qvalue;
      break;
    case "SysplexName":
      $qtypeHTML="Sysplex<br/>Name";
      $qvalueHTML=$qvalue;
      break;
    case "SysplexNameGroup":
      $qtypeHTML="Sysplex<br/>Name<br/>Group";
      $qvalueHTML=$qvalue;
      break;
    case "PackageNameGroup":
      $qvalueHTML="<span id='CG_USE_$qvalue'><a href='#CG_DEF_$qvalue'>$qvalue</a></span>";
      $qtypeHTML="Package Name<br/>Group";
     break;
    case "PackageName":
      $qtypeHTML="Package<br/>Name";
      $qvalueHTML=$qvalue;
      break;
    case "CollectionName":
      $qtypeHTML="Collection<br/>Name";
      $qvalueHTML=$qvalue;
      break;
    case "ConnectionType":
      $qtypeHTML="Connection<br/>Type";
      $qvalueHTML=$qvalue;
      break;
    case "PlanName":
      $qtypeHTML="Plan<br/>Name";
      $qvalueHTML=$qvalue;
      break;
    case "PlanNameGroup":
      $qtypeHTML="Plan<br/>Name<br/>Group";
      $qvalueHTML=$qvalue;
      break;
    case "SubsystemParameter":
      $qtypeHTML="Subsystem<br/>Parameter";
      $qvalueHTML=$qvalue;
      break;
    case "TransactionClass":
      $qtypeHTML="Transaction<br/>Class";
      $qvalueHTML=$qvalue;
      break;
    case "LUName":
      $qtypeHTML="LU Name";
      $qvalueHTML=$qvalue;
      break;
      case "TransactionClassGroup":
      $qtypeHTML="Transaction<br/>Class<br/>Group";
      $qvalueHTML=$qvalue;
      break;
     case "CorrelationInformation":
      $qtypeHTML="Correlation<br/>Information";
      $qvalueHTML=$qvalue;
       break;
    case "SubsystemInstanceGroup":
      $qtypeHTML="Subsystem<br/>Instance<br/>Group";
      $qvalueHTML="<span id='CG_USE_$qvalue'><a href='#CG_DEF_$qvalue'>$qvalue</a></span>";
      break;
    case "UseridGroup":
      $qtypeHTML="Userid Group";
      $qvalueHTML="<span id='CG_USE_$qvalue'><a href='#CG_DEF_$qvalue'>$qvalue</a></span>";
      break;
    case "SubsystemInstance":
      $qtypeHTML="Subsystem<br/>Instance";
      $qvalueHTML=$qvalue;    
       break;
    case "SystemName":
      $qtypeHTML="System Name";
      $qvalueHTML=$qvalue;    
       break;
    case "SystemNameGroup":
      $qtypeHTML="System Name<br/>Group";
      $qvalueHTML=$qvalue;
      break;
    case "ProcessName":
      $qtypeHTML="Process Name";
      $qvalueHTML=$qvalue;    
      break;
    case "ProcedureName":
      $qtypeHTML="Procedure<br/>Name";
      $qvalueHTML=$qvalue;    
      break;
    case "SubsystemCollection":
      $qtypeHTML="Subsystem<br/>Collection";
      $qvalueHTML=$qvalue;
      break;
    case "SchedulingEnvironment":
      $qtypeHTML="Scheduling<br/>Environment";
      $qvalueHTML=$qvalue;
      break;
    case "AccountingInformation":
      $qtypeHTML="Accounting<br/>Information";
      $qvalueHTML=$qvalue;
      break;
    case "AccountingInformationGroup":
      $qtypeHTML="Accounting<br/>Information<br/>Group";
      $qvalueHTML=$qvalue;
      break;
    case "ClientWorkstationName":
      $qtypeHTML="Client Workstation Name";
      $qvalueHTML=$qvalue;
      break;
    case "Perform":
      $qtypeHTML="Performance Group";
      $qvalueHTML=$qvalue;
      break;
     default:
      $qtypeHTML=$qtype;
      $qvalueHTML=$qvalue;    
    }

    $node = $xpath->query("wlm:Start",$cr)->item(0);
    if($node != null){
      $qstart = $node->nodeValue;
    }else{
      $qstart = "";
    }
    
    if($qstart!=""){
      $qvalueHTML=$qvalueHTML."<br/>@ ".$qstart;
    }


    $NDL=$xpath->query("wlm:Description",$cr);
    if($NDL->length>0){
      $desc=$NDL->item(0)->nodeValue;
    }else{
      $desc="";
    }

    $NDL=$xpath->query("wlm:ServiceClassName",$cr);
    if($NDL->length>0){
      $sclass=$NDL->item(0)->nodeValue;
      array_push($seenSCs,$sclass);
    }else{
      $sclass="";
    }

    $NDL=$xpath->query("wlm:ReportClassName",$cr);
    if($NDL->length>0){
      $rclass=$NDL->item(0)->nodeValue;
      array_push($seenRCs,$rclass);
    }else{
      $rclass="";
    }


    $storageCritical=$xpath->query("wlm:StorageCritical",$cr)->item(0)->nodeValue;
    if($storageCritical=="") $storageCritical="&nbsp";
    
    $regionGoal=$xpath->query("wlm:RegionGoal",$cr)->item(0)->nodeValue;
    if($regionGoal=="") $regionGoal="&nbsp";
    
    $node = $xpath->query("wlm:ReportingAttribute",$cr)->item(0);
    if($node != null){
      $reportingAttribute = $node->nodeValue;
    }else{
      $reportingAttribute = "";
    }
    
    if(($reportingAttribute=="") || ($reportingAttribute=="None")) $reportingAttribute="&nbsp";

    echo blank_cells(3*($l-1)+1);
    echo cell($qtypeHTML);
    echo cell($qvalueHTML);
    echo cell($desc);

    if($maxClassificationRuleLevel-$l>0){
      echo str_repeat($bc,3*($maxClassificationRuleLevel-$l));
    }
    
    echo cell(href("SC",$sclass));
    echo cell(href("RC",$rclass));
    echo cell($storageCritical,'center',75);
    echo cell($regionGoal,'center',75);
    echo cell($reportingAttribute,'center',75);
    echo blank_cells(4);
    echo "</tr>\n";
    
    do_classification_rules($cr,$l+1);

    // Blank row after the subsystem
    echo "<tr>\n".blank_cells(3+3*$maxClassificationRuleLevel);

    // Flag cells are narrower
    echo blank_cells(2,'center',75);
    
    echo blank_cells(4)."</tr>";
  }
  return;
}

if(PHP_SAPI =='cli'){
  $commandLine = true;
  echo "<p>Run from command line.</p>\n";

  $file = file_get_contents('php://stdin');
}else{
  $commandLine = false;
  echo "<p>Run from web server.</p>\n";

  $sds = $_GET['sds'];
  echo "<p>Source XML file: $sds</p>\n";

  $file = file_get_contents(str_replace(" ", "%20", $sds));
}



// Blank cell
$bc=cell("&nbsp;");

// List of seen Resource Classes
$seenRCs=array();
$seenSCs=array();

// Remove newlines
$cleanedFile = str_replace(["\n","\r",chr(0x1a)], ["", "", ""], $file);

// Load the cleaned up XML
$dom = new DOMDocument;
$dom->loadXML($cleanedFile);

$xpath = new DOMXPath($dom);
$rootNamespace = $dom->lookupNamespaceUri($dom->namespaceURI);
$xpath->registerNamespace('wlm', $rootNamespace); 

echo "<p>Namespace: $rootNamespace</p>\n";


// Put out title and heading
$sdName=$xpath->query('/wlm:ServiceDefinition/wlm:Name')->item(0)->nodeValue;

$sdDescNodes=$xpath->query('/wlm:ServiceDefinition/wlm:Description');
if($sdDescNodes->length>0){
  $sdDesc=$sdDescNodes->item(0)->nodeValue;
}else{
  $sdDesc="";
}

if($sdDesc==""){
  $title=$sdName;
}else{
  $title="$sdName - $sdDesc";
}

echo "<title>$title</title>";
echo "<h1 id='top'>$title</h1>";

// Put out table of contents
echo "<sl>\n";
echo "<li><a href='#statistics'>Statistics</a></li>\n";
echo "<li><a href='#notes'>Notes</a></li>\n";
echo "<li><a href='#creationDates'>Creation Dates By Year</a></li>\n";
echo "<li><a href='#modificationDates'>Modification Dates By Year</a></li>\n";
echo "<li><a href='#srvParms'>Service Parameters</a></li>\n";
echo "<li><a href='#classGrps'>Classification Groups</a></li>\n";
echo "<li><a href='#classifications'>Classification Rules</a></li>\n";

$classification_groups=$xpath->query('/wlm:ServiceDefinition/wlm:ClassificationGroups/wlm:ClassificationGroup');

$classificationNameNodes=$xpath->query('//wlm:ClassificationRule/wlm:QualifierValue');

$srvPols=$xpath->query('/wlm:ServiceDefinition/wlm:ServicePolicies/wlm:ServicePolicy');

$workloads=$xpath->query('/wlm:ServiceDefinition/wlm:Workloads/wlm:Workload');
$workloadp=$xpath->query('/wlm:ServiceDefinition/wlm:Workloads')[0];

$velocityNodes=$xpath->query('//wlm:Velocity',$workloadp);
$SCOVelocityNodes=$xpath->query('//wlm:ServiceClassOverride/wlm:Goal/wlm:Velocity',$workloadp);

$percentileNodes=$xpath->query('//wlm:Percentile',$workloadp);
$SCOPercentileNodes=$xpath->query('//wlm:ServiceClassOverride/wlm:Goal/wlm:PercentileResponseTime',$workloadp);

$averageNodes=$xpath->query('//wlm:Average',$workloadp);
$SCOAverageNodes=$xpath->query('//wlm:ServiceClassOverride/wlm:Goal/wlm:AverageResponseTime',$workloadp);

$discretionaryNodes=$xpath->query('//wlm:Discretionary',$workloadp);
$SCODiscretionaryNodes=$xpath->query('//wlm:ServiceClassOverride/wlm:Goal/wlm:Discretionary',$workloadp);

$scPeriodCount = $velocityNodes->length + $averageNodes->length + $discretionaryNodes->length + $percentileNodes->length;

$rcs=$xpath->query('/wlm:ServiceDefinition/wlm:ReportClasses/wlm:ReportClass');

$aes=$xpath->query('/wlm:ServiceDefinition/wlm:ApplicationEnvironments/wlm:ApplicationEnvironment');

$resourceNodes=$xpath->query('//wlm:SchedulingEnvironment/wlm:ResourceNames/wlm:ResourceName/wlm:Name');

$resGrps=$xpath->query('/wlm:ServiceDefinition/wlm:ResourceGroups/wlm:ResourceGroup');
if($resGrps->length){
  echo "<li><a href='#resGrps'>Resource Groups</a></li>\n";
}

$RGONodes=$xpath->query('//wlm:ResourceGroupOverride',$workloadp);
$SCRGONodes=$xpath->query('//wlm:ServicePolicy/wlm:ServiceClassOverrides/wlm:ServiceClassOverride/wlm:ResourceGroupName',$workloadp);


echo "<li><a href='#srvPols'>Service Policies</a></li>\n";
echo "<li><a href='#workloads'>Workloads And Service Classes</a></li>\n";
echo "<li><a href='#rptClasses'>Report Classes</a></li>\n";
echo "<li><a href='#applEnvs'>Application Environments</a></li>\n";

$rs=$xpath->query('/wlm:ServiceDefinition/wlm:Resources/wlm:Resource');
if($rs->length){
  echo "<li><a href='#resources'>Resources</a></li>\n";
}

$ses=$xpath->query('/wlm:ServiceDefinition/wlm:SchedulingEnvironments/wlm:SchedulingEnvironment');
if($ses->length){
  echo "<li><a href='#schEnvs'>Scheduling Environments</a></li>\n";
}
echo "</sl>\n";

// Work out whether we need to report on HonorPriority at the individul service class level
$HPs=$xpath->query('//wlm:HonorPriority');
$wantHP=false;
if($HPs->length){
  foreach($HPs as $hp){
    if($hp->nodeValue=="No"){
      $wantHP=true;
      break;
    }
  }
}

// Work out what sysplexes are explicitly named
$sysplexes = [];
$sysplexElements = $xpath->query('//wlm:QualifierType [text()="SysplexName"]');
foreach($sysplexElements as $se){
    $sysplexName = trim($se->nextSibling->nextSibling->nodeValue);
    if(array_search($sysplexName, $sysplexes) === false){
        array_push($sysplexes, $sysplexName);
    }
}

// Work out what systems are explicitly named
$systems = [];
$systemElements = $xpath->query('//wlm:QualifierType [text()="SystemName"]');
foreach($systemElements as $se){
    $sns = $xpath->query('wlm:QualifierNames/wlm:QualifierName/wlm:Name',$se->parentNode);
    foreach($sns as $sn){
        $systemName = trim($sn->nodeValue);
        if(array_search($systemName, $systems) === false){
            array_push($systems, $systemName);
        }
    }
}

// Work out what subsystems are explicitly named
$subsystems = [];
$subsystemElements = $xpath->query('//wlm:QualifierType [text()="SubsystemInstance"]');
foreach($subsystemElements as $se){
    $subsystemNameNode = $se->nextSibling->nextSibling;
    if($subsystemNameNode != null){
      $subsystemName = trim($subsystemNameNode->nodeValue);
    }else{
      $subsystemName = "";
    }
    
    if(array_search($subsystemName, $subsystems) === false){
        array_push($subsystems, $subsystemName);
    }
}

// Work out what performance groups are explicitly named
$performanceGroups = [];
$performanceGroupElements = $xpath->query('//wlm:QualifierType [text()="Perform"]');
foreach($performanceGroupElements as $pge){
    $performanceGroup = trim($pge->nextSibling->nextSibling->nodeValue);
    if(array_search($performanceGroup, $performanceGroups) === false){
        array_push($performanceGroups, $performanceGroup);
    }
}

// Put out level
$sdLevel=$xpath->query('/wlm:ServiceDefinition/wlm:Level')->item(0)->nodeValue;
$sdProdId1=explode(" ", $xpath->query('/wlm:ServiceDefinition/wlm:ProdId')->item(0)->nodeValue);
$ProdIdWords = count($sdProdId1);
$sdProdId = substr($sdProdId1[$ProdIdWords - 1], 5);

echo "<a href='#top'><h2 id='statistics'>Statistics</h2></a>\n";

echo "<table class=scrollable border='1'>\n";

echo "<tbody>\n";

echo "<tr>\n";
echo "<td style='min-width: 200px;max-width: 200px;''>Level</td><td>".$sdLevel."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td style='min-width: 200px;max-width: 200px;''>ProdId Level</td><td>".$sdProdId."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Classification Groups</td><td>".$classification_groups->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Classification Rules</td><td>".$classificationNameNodes->length."</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td>&nbsp;</td><td>&nbsp;</td>\n";	
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Service Policies</td><td>".$srvPols->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Workloads</td><td>".$workloads->length."</td>\n";
$serviceClassCount=$xpath->query('//wlm:ServiceClass')->length;
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Service Classes</td><td>".$serviceClassCount."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Service Class Periods</td><td>".$scPeriodCount."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>&nbsp;</td><td>&nbsp;</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Velocity</td><td>".$velocityNodes->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Of which overrides</td><td>".$SCOVelocityNodes->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>&nbsp;</td><td>&nbsp;</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Average Response Time</td><td>".$averageNodes->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Of which overrides</td><td>".$SCOAverageNodes->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>&nbsp;</td><td>&nbsp;</td>\n";
echo "</tr>\n";


echo "<tr>\n";
echo "<td>Percentile Response Time</td><td>".$percentileNodes->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Of which overrides</td><td>".$SCOPercentileNodes->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>&nbsp;</td><td>&nbsp;</td>\n";
echo "</tr>\n";


echo "<tr>\n";
echo "<td>Discretionary</td><td>".$discretionaryNodes->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Of which overrides</td><td>".$SCODiscretionaryNodes->length."</td>\n";
echo "</tr>\n";


echo "<tr>\n";
echo "<td>&nbsp;</td><td>&nbsp;</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Resource Groups</td><td>".$resGrps->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Resource Group Overrides</td><td>".$RGONodes->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Service Class Resource Group Overrides</td><td>".$SCRGONodes->length."</td>\n";
echo "</tr>\n";


echo "<tr>\n";
echo "<td>&nbsp;</td><td>&nbsp;</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Report Classes</td><td>".$rcs->length."</td>\n";
echo "</tr>\n";


echo "<tr>\n";
echo "<td>Application Environments</td><td>".$aes->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Resources</td><td>".$resourceNodes->length."</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td>Scheduling Environments</td><td>".$ses->length."</td>\n";
echo "</tr>\n";

// If sysplexes named in the rules then list them
if(count($sysplexes) > 0){
    $sysplexNames="";
    foreach($sysplexes as $sn){
        $sysplexNames = $sysplexNames . $sn . " ";
    }

    echo "<tr>\n";
    echo "<td>Sysplex Names</td><td>" . $sysplexNames . "</td>\n";
    echo "</tr>\n";
}

// If systems named in the rules then list them
if(count($systems) > 0){
    $systemNames="";
    foreach($systems as $sn){
        $systemNames = $systemNames . $sn . " ";
    }

    echo "<tr>\n";
    echo "<td>System Names</td><td>" . $systemNames . "</td>\n";
    echo "</tr>\n";
}

// If subsystems named in the rules then list them
if(count($subsystems) > 0){
    $subsystemNames="";
    foreach($subsystems as $sn){
        $subsystemNames = $subsystemNames . $sn . " ";
    }

    echo "<tr>\n";
    echo "<td>Subsystem Names</td><td>" . $subsystemNames . "</td>\n";
    echo "</tr>\n";
}

// If performance groups named in the rules then list them
if(count($performanceGroups) > 0){
    $performanceGroupNames="";
    foreach($performanceGroups as $pgn){
        $performanceGroupNames = $performanceGroupNames . $pgn . " ";
    }

    echo "<tr>\n";
    echo "<td>Performance Groups</td><td>" . $performanceGroupNames . "</td>\n";
    echo "</tr>\n";
}

echo "</tbody>\n";

echo "</table>\n";

// Put out notes - if any
echo "<a href='#top'><h2 id='notes'>Notes</h2></a>\n";

$sdNotes=$xpath->query('/wlm:ServiceDefinition/wlm:Notes/wlm:Note');
if($sdNotes->length > 0){
  // Have notes
  $noteHTML="<pre>";
  foreach($sdNotes as $note){
    $noteText=$note->nodeValue;
    if(!ctype_digit(trim($noteText))){
      // Is not a line number but real text
      $noteText=str_replace("<","&lt;",$noteText);
      $noteText=str_replace(">","&gt;",$noteText);
      $noteText=str_replace(" ","&nbsp",$noteText);
      $noteHTML.="$noteText\n";  
    }
  }
  echo "$noteHTML</pre>";
}else{
  echo "<p>There are no notes.</p>\n";
}

// Get all classification rule names
$classificationNames=array();
$seenRGNameNodes=$xpath->query('//wlm:ResourceGroupName');
foreach ($classificationNameNodes as $CNN){
  $name=trim($CNN->nodeValue);
  array_push($classificationNames,$name);
}
$classificationNames=array_unique($classificationNames);

// Get all used resource group names
$seenRGs=array();
foreach ($seenRGNameNodes as $RGN){
  $name=trim($RGN->nodeValue);
  array_push($seenRGs,$name);
}
$seenRGs=array_unique($seenRGs);

// Get all scheduling environment resources
$resources=array();
foreach ($resourceNodes as $RN){
  $name=trim($RN->nodeValue);
  array_push($resources,$name);
}
$classificationNames=array_unique($classificationNames);

$uniqueUserids = [];

// List creation dates - Part 1
$creationDates=$xpath->query('//wlm:CreationDate');
$creationYears=array(
  "1990"=>0,"1991"=>0,"1992"=>0,"1993"=>0,"1994"=>0,"1995"=>0,"1996"=>0,"1997"=>0,"1998"=>0,"1999"=>0,
  "2000"=>0,"2001"=>0,"2002"=>0,"2003"=>0,"2004"=>0,"2005"=>0,"2006"=>0,"2007"=>0,"2008"=>0,"2009"=>0,
  "2010"=>0,"2011"=>0,"2012"=>0,"2013"=>0,"2014"=>0,"2015"=>0,"2016"=>0,"2017"=>0,"2018"=>0,"2019"=>0,
  "2020"=>0,"2021"=>0,"2022"=>0,"2023"=>0,"2024"=>0,"2025"=>0,"2026"=>0,"2027"=>0,"2028"=>0,"2029"=>0,
);
$creationYearNames=array();
foreach ($creationDates as $cd) {
  $creationYear=substr($cd->nodeValue,0,4);
  if($creationYear!="1900"){
    $creationYears[$creationYear]++;
    $creationUserNode = $xpath->query('wlm:CreationUser',$cd->parentNode)[0];
    if($creationUserNode != null){
      $creationUser = $creationUserNode->nodeValue;
    }else{
      $creationUser = "";
    }

    if(strpos($creationUser,"/")!==false){
      // Picked up date as no creation user
      $creationUser="Unknown";
    }else{
      if (!in_array($creationUser, $uniqueUserids))
      {
        $uniqueUserids[] = $creationUser; 
      }
    }
    
    if($creationYears[$creationYear]==1){
      $creationYearNames[$creationYear]=array();
    }
    array_push($creationYearNames[$creationYear],strtoupper($creationUser));
  }
}

// List modification dates - Part 1
$modificationDates=$xpath->query('//wlm:ModificationDate');
$modificationYears=array(
  "1990"=>0,"1991"=>0,"1992"=>0,"1993"=>0,"1994"=>0,"1995"=>0,"1996"=>0,"1997"=>0,"1998"=>0,"1999"=>0,
  "2000"=>0,"2001"=>0,"2002"=>0,"2003"=>0,"2004"=>0,"2005"=>0,"2006"=>0,"2007"=>0,"2008"=>0,"2009"=>0,
  "2010"=>0,"2011"=>0,"2012"=>0,"2013"=>0,"2014"=>0,"2015"=>0,"2016"=>0,"2017"=>0,"2018"=>0,"2019"=>0,
  "2020"=>0,"2021"=>0,"2022"=>0,"2023"=>0,"2024"=>0,"2025"=>0,"2026"=>0,"2027"=>0,"2028"=>0,"2029"=>0,
);
foreach ($modificationDates as $md) {
  $modificationYear=substr($md->nodeValue,0,4);
  if($modificationYear!="1900"){
    $modificationYears[$modificationYear]++;
    $modificationUserNode = $xpath->query('wlm:ModificationUser',$md->parentNode)[0];
    if($modificationUserNode != null){
      $modificationUser = $modificationUserNode -> nodeValue;
    }else{
      $modificationUser = "";
    }

    if (!in_array($modificationUser, $uniqueUserids))
    {
      $uniqueUserids[] = $modificationUser; 
    }

    if($modificationYears[$modificationYear]==1){
      $modificationYearNames[$modificationYear]=array();
    }
    array_push($modificationYearNames[$modificationYear],strtoupper($modificationUser));
  }
}

// List creation dates - Part 2
echo "<a href='#top'><h2 id='creationDates'>Creation Dates By Year</h2></a>\n";

echo "<table border='1'>\n";
echo "<tr>\n";
echo "<th>Year</th>\n";
echo "<th>Count</th>\n";
echo "<th>Userids</th>\n";
echo "</tr>\n";

foreach($creationYears as $cy=>$creationCount){
  if($creationCount>0){
    echo "<tr>\n";
    echo cell($cy)."\n";
    echo cell($creationCount,"right");
    
    $names=array_unique($creationYearNames[$cy]);
    asort($names);
    
    // Colour each name
    $colouredNames="";
    foreach($names as $name){
      $colourIndex = array_search($name, $uniqueUserids);
      if($colourIndex >= $lForegroundColours){
        $colour = "#000000";
      }else{
        $colour = $foregroundColourPalette[$colourIndex];
      }
      $colouredName = "<span style='color: " . $colour . ";'>" . $name . "</span>";
      $colouredNames .= $colouredName . " ";
    }
    
    echo cell($colouredNames);
    
    echo "</tr>\n";
  }
}

// List modification dates - Part 2

echo "</table>\n";

echo "<a href='#top'><h2 id='modificationDates'>Modification Dates By Year</h2></a>\n";

echo "<table border='1'>\n";
echo "<tr>\n";
echo "<th>Year</th>\n";
echo "<th>Count</th>\n";
echo "<th>Userids</th>\n";
echo "</tr>\n";
foreach($modificationYears as $my=>$modificationCount){
  if($modificationCount>0){
    echo "<tr>\n".cell($my)."\n";
    echo cell($modificationCount,"right")."\n";
    $names=array_unique($modificationYearNames[$my]);
    asort($names);

    // Colour each name
    $colouredNames="";
    foreach($names as $name){
      $colourIndex = array_search($name, $uniqueUserids);
      if($colourIndex >= $lForegroundColours){
        $colour = "#000000";
      }else{
        $colour = $foregroundColourPalette[$colourIndex];
      }
      $colouredName = "<span style='color: " . $colour . ";'>" . $name . "</span>";
      $colouredNames .= $colouredName . " ";
    }
    
    echo cell($colouredNames);

    echo "</tr>\n";
   }
}

echo "</table>\n";

// List service parameters
echo "<a href='#top'><h2 id='srvParms'>Service Parameters</h2></a>\n";

echo "<table border='1'>\n";
echo "<tr>\n";
echo "<th>Parameter</th>\n";
echo "<th>Value</th>\n";
echo "</tr>\n";

$CPU = $xpath->query('/wlm:ServiceDefinition/wlm:ServiceParameter/wlm:ServiceCoefficients/wlm:CPU')->item(0)->nodeValue;
$IOC = $xpath->query('/wlm:ServiceDefinition/wlm:ServiceParameter/wlm:ServiceCoefficients/wlm:IOC')->item(0)->nodeValue;
$MSO = $xpath->query('/wlm:ServiceDefinition/wlm:ServiceParameter/wlm:ServiceCoefficients/wlm:MSO')->item(0)->nodeValue;
$SRB = $xpath->query('/wlm:ServiceDefinition/wlm:ServiceParameter/wlm:ServiceCoefficients/wlm:SRB')->item(0)->nodeValue;
$IOPrio = $xpath->query('/wlm:ServiceDefinition/wlm:ServiceParameter/wlm:ServiceOptions/wlm:IOPriorityManagement')->item(0)->nodeValue;
$DynAlias = $xpath->query('/wlm:ServiceDefinition/wlm:ServiceParameter/wlm:ServiceOptions/wlm:DynamicAliasManagement')->item(0)->nodeValue;
$node = $xpath->query('/wlm:ServiceDefinition/wlm:ServiceParameter/wlm:ServiceOptions/wlm:IOPriorityGroupsEnabled')->item(0);
if($node != null){
  $IOPriorityGroupsEnabled = $node->nodeValue;
}else{
  $IOPriorityGroupsEnabled = "";
}

$node = $xpath->query('/wlm:ServiceDefinition/wlm:ServiceParameter/wlm:ServiceOptions/wlm:DeactivateDiscretionaryGoalManagement')->item(0);
if($node != null){
  $DeactivateDiscretionaryGoalManagement= $node->nodeValue;
}else{
  $DeactivateDiscretionaryGoalManagement="";
}

echo "<tr>\n".cell("CPU").cell($CPU)."</tr>\n";
echo "<tr>\n".cell("IOC").cell($IOC)."</tr>\n";
echo "<tr>\n".cell("MSO").cell($MSO)."</tr>\n";
echo "<tr>\n".cell("SRB").cell($SRB)."</tr>\n";

echo "<tr>\n".cell("I/O Priority").cell($IOPrio)."</tr>\n";
echo "<tr>\n".cell("Dynamic Alias<br/>Management").cell($DynAlias)."</tr>\n";
echo "<tr>\n".cell("I/O Priority<br/>Groups Enabled").cell($IOPriorityGroupsEnabled)."</tr>\n";
echo "<tr>\n".cell("Deactivate Discretionary<br/>Goal Management").cell($DeactivateDiscretionaryGoalManagement)."</tr>\n";

echo "</table>\n";

// Report whether we have Service Class level HonorPriority specifications
if($HPs->length){
  echo "<p>WLM Policy Supports HonorPriority at the Service Class Level. ";

  if($wantHP){
    echo "At least one Service Class Has HonorPriority set to 'No'.</p>\n";
  }else{
    echo "No Service Class Has HonorPriority set to 'No'.</p>\n";    
  }
}


// List classification groups
echo "<a href='#top'><h2 id='classGrps'>Classification Groups</h2></a>\n";

echo "<table class=scrollable border='1'>\n";
echo "<thead>\n";
echo "<tr>\n";
echo "<th style='min-width: 200px; max-width: 200px;'>Group Name / Description</th>\n";
echo "<th>Value</th>\n";
echo "<th style='min-width: 200px; max-width: 200px;'>Description</th>\n";
echo "<th>Created</th>\n";
echo "<th>User</th>\n";
echo "<th>Updated</th>\n";
echo "<th>User</th>\n";
echo "</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";

foreach($classification_groups as $cg){
  $cgName=$xpath->query("wlm:Name",$cg)->item(0)->nodeValue;
  
  $node = $xpath->query("wlm:Description",$cg)->item(0);
  if($node != null){
    $cgDesc = $node->nodeValue;
  }else{
    $cgDesc = "";
  }
  
  $cgQualifierType=$xpath->query("wlm:QualifierType",$cg)->item(0)->nodeValue;
  switch($cgQualifierType){
  case "SubsystemInstance":
    $cgQualifierTypeHTML="Subsystem Instance";
    break;
  case "TransactionName":
    $cgQualifierTypeHTML="Transaction Name";
    break;
  case "SysplexName":
    $cgQualifierTypeHTML="Sysplex Name";
    break;
  case "SystemName":
    $cgQualifierTypeHTML="System Name";
    break;
  case "PlanName":
    $cgQualifierTypeHTML="Plan Name";
    break;
  case "PlanNameGroup":
    $cgQualifierTypeHTML="Plan Name Group";
    break;
  case "TransactionClass":
    $cgQualifierTypeHTML="Transaction Class";
    break;
  case "PackageName":
    $cgQualifierTypeHTML="Package Name";
    break;
  case "LUName":
    $cgQualifierTypeHTML="LU Name";
    break;
  case "Perform":
    $cgQualifierTypeHTML="Performance Group";
    break;
  case "AccountingInformation":
    $cgQualifierTypeHTML="Accounting Information";
    break;
  default:
    $cgQualifierTypeHTML=$cgQualifierType;  
  }

  // Pick up any start offset
  $node = $xpath->query("wlm:Start",$cg)->item(0);
  if($node != null){
    $cgQualifierStart = $node->nodeValue;
  }else{
    $cgQualifierStart = "";
  }

  if($cgQualifierStart!=""){
    $cgName=$cgName." @".$cgQualifierStart;
  }

  // Pick up creation date and user
  $cgCreationDate=substr($xpath->query("wlm:CreationDate",$cg)->item(0)->nodeValue,0,10);
  $cgCreationUser=$xpath->query("wlm:CreationUser",$cg)->item(0)->nodeValue;

  if($cgCreationDate==""){
    $cgCreationDateHTML="&nbsp";
  }else{
    $cgCreationDateHTML=$cgCreationDate;
  }

  switch($cgCreationUser){
  case "":
    $cgCreationUserHTML="&nbsp";
    break;
  case "CLW":
    $cgCreationUserHTML="Cheryl<br/>Watson";
    break;
  default:
    $cgCreationUserHTML=$cgCreationUser;
  }

  // Pick up modification date and user
  $cgModificationDate=substr($xpath->query("wlm:ModificationDate",$cg)->item(0)->nodeValue,0,10);
  $cgModificationUser=$xpath->query("wlm:ModificationUser",$cg)->item(0)->nodeValue;
  
  switch($cgModificationDate){
  case "":
    $cgModificationDateHTML="&nbsp";
    break;
  default:
    if($cgModificationDate==$cgCreationDate){
      $cgModificationDateHTML="None";
    }else{
      $cgModificationDateHTML=$cgModificationDate;
    }
  }

  if($cgModificationUser=="" | $cgModificationDateHTML=="None"){
    $cgModificationUserHTML="&nbsp";
  }else{
  	if($cgModificationUser=="CLW"){
  		$cgModificationUserHTML="Cheryl<br/>Watson";
  	}else{
	    $cgModificationUserHTML=$cgModificationUser;
  	}
  }
  
  $nameUsed=array_search($cgName,$classificationNames);
  if($nameUsed!==false){
    $usedString='';
  }else{
    $usedString='<br/>(Unused)';
  }

  $cgDef="<strong><span id='CG_DEF_$cgName'><a href='#CG_USE_$cgName'>$cgName</a></span></strong>";
  echo "<tr>\n".cell($cgDef."<br/>$cgDesc".$usedString,'left',200);
  echo cell("<strong>$cgQualifierTypeHTML</strong>");
  echo blank_cells(1,'left',200)."\n";
  echo cell($cgCreationDateHTML);
  echo cell($cgCreationUserHTML);
  echo cell($cgModificationDateHTML);
  echo cell($cgModificationUserHTML);

  echo "</tr>\n";
  
  $qualifierNames=$xpath->query("wlm:QualifierNames/wlm:QualifierName",$cg);
  $old_qnDesc="&nbsp;";
  $qnNames="";
  foreach($qualifierNames as $qn){
    $qnName=$xpath->query("wlm:Name",$qn)->item(0)->nodeValue;

    $qnDescNodes=$xpath->query("wlm:Description",$qn);
    if($qnDescNodes->length>0){
      $qnDesc=$qnDescNodes->item(0)->nodeValue;
    }else{
      $qnDesc="";
    }
    if($qnDesc=="") $qnDesc="&nbsp;";

    /* Handle accumulation of names */
    if($qnDesc==$old_qnDesc){
      $qnNames=$qnNames." ".$qnName;
    }else{
      if($qnNames!=""){
        echo "<tr>\n$bc".cell($qnNames).cell($old_qnDesc,'left',200).str_repeat($bc,4)."</tr>\n";
      }
      $old_qnDesc=$qnDesc;
      $qnNames=$qnName;
    }
  }
  if($qnNames!=""){
    echo "<tr>\n$bc".cell($qnNames).cell($old_qnDesc).str_repeat($bc,4)."</tr>\n";    
  }
  
  echo "<tr>\n".str_repeat($bc,7)."</tr>";
  
}

echo "</tbody>\n";
echo "</table>\n";

// List classification rules
$classifications=$xpath->query('/wlm:ServiceDefinition/wlm:Classifications/wlm:Classification');


// In searching for levels it's possible no "ClassificationRules" node exists - so handle that
$cr0=$xpath->query('//wlm:ClassificationRules');
if ($cr0->length==0){
  $cr='wlm:ClassificationRule';

}else{
  $cr='wlm:ClassificationRules/wlm:ClassificationRule';
}

// Figure out how many nesting levels of classification rules there are
$maxClassificationRuleLevel=0;

// For each subsystem find the tree depth
foreach($classifications as $c){
  if($xpath->query($cr,$c)->length>0){
    if($xpath->query($cr."/wlm:ClassificationRule",$c)->length>0){
      if($xpath->query($cr."/wlm:ClassificationRule/wlm:ClassificationRule",$c)->length>0){
        if($xpath->query($cr."/wlm:ClassificationRule/wlm:ClassificationRule/wlm:ClassificationRule",$c)->length>0){
          if($xpath->query($cr."/wlm:ClassificationRule/wlm:ClassificationRule/wlm:ClassificationRule/wlm:ClassificationRule",$c)->length>0){
            $cclevels=5;
          }else{
            $cclevels=4;
          }
        }else{
          $cclevels=3;
        }
      }else{
        $cclevels=2;
      }
    }else{
      $cclevels=1;
    }
  }else{
    // No classification rules for this subsystem
    $cclevels=0;
  }

  $maxClassificationRuleLevel=max($maxClassificationRuleLevel,$cclevels);
}

echo "<a href='#top'><h2 id='classifications'>Classification Rules</h2></a>\n";

if($maxClassificationRuleLevel==1){
  echo "<p>1 nesting level.</p>\n";
}else{
  echo "<p>$maxClassificationRuleLevel nesting levels.</p>\n";
}

// Put out quick links to subsystems in following table
echo("<p><strong>Go to subsystem:</strong>\n");

foreach($classifications as $c){
  // Pick up subsystem name
  $subsys=$xpath->query("wlm:SubsystemType",$c)->item(0)->nodeValue;
  echo (href("SS",$subsys)."\n");
}

// Put out subsystem tree buttons for following table
echo("<p><strong>Subsystem tree:&nbsp;&nbsp;</strong>\n");

foreach($classifications as $c){
  // Pick up subsystem name
  $subsys=$xpath->query("wlm:SubsystemType",$c)->item(0)->nodeValue;
  //echo (href("SS",$subsys)."\n");
  echo ("<a href='javascript:makeTree(\"SS_". $subsys ."\")'>".$subsys."</a>\n");
}
echo("</p>\n");

echo "<table class=scrollable border='1'>\n";
echo "<thead>\n";
echo "<tr>\n";
echo "<th style='min-width: 200px; max-width: 200px;'>Subsystem Type /<br/>Description</th>\n";
for($l=1;$l<=$maxClassificationRuleLevel;$l++){
  echo "<th>Qualifier<br/>Type $l</th>\n";
  echo "<th>Qualifier<br/>Value $l</th>\n";
  echo "<th>Description $l</th>\n";
}
echo "<th>Service<br/>Class</th>\n";
echo "<th>Report<br/>Class</th>\n";
echo "<th style='min-width: 75px; max-width: 75px;'>Storage<br/>Critical?</th>\n";
echo "<th style='min-width: 75px; max-width: 75px;'>Region<br/>Goal?</th>\n";
echo "<th style='min-width: 75px; max-width: 75px;'>Reporting<br/>Attribute</th>\n";
echo "<th>Created</th>\n";
echo "<th>User</th>\n";
echo "<th>Updated</th>\n";
echo "<th>User</th>\n";
echo "</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";

foreach($classifications as $c){
  // Pick up subsystem name
  $subsys=$xpath->query("wlm:SubsystemType",$c)->item(0)->nodeValue;
  

  // Pick up description
  $node=$xpath->query("wlm:Description",$c)->item(0);
  if($node != null){
    $desc = $node->nodeValue;
  }else{
    $desc = "";
  }
    
  // Pick up default service class
  $NDL=$xpath->query("wlm:DefaultServiceClassName",$c);
  if($NDL->length>0){
    $defSC=$NDL->item(0)->nodeValue;
  }else{
    $defSC="";
  }

  // Pick up default report class
  $NDL=$xpath->query("wlm:DefaultReportClassName",$c);
  if($NDL->length>0){
    $defRC=$NDL->item(0)->nodeValue;
  }else{
    $defRC="";
  }

  // Pick up creation date and user
  $cCreationDateNodes=$xpath->query("wlm:CreationDate",$c);
  if($cCreationDateNodes->length>0){
    $cCreationDate=substr($cCreationDateNodes->item(0)->nodeValue,0,10);
  }else{
    $cCreationDate="";
  }

  $cCreationUserNodes=$xpath->query("wlm:CreationUser",$c);
  if($cCreationUserNodes->length>0){
    $cCreationUser=$cCreationUserNodes->item(0)->nodeValue;
  }else{
    $cCreationUser="";
  }

  if($cCreationDate==""){
    $cCreationDateHTML="&nbsp";
  }else{
    $cCreationDateHTML=$cCreationDate;
  }

  switch($cCreationUser){
  case "":
    $cCreationUserHTML="&nbsp";
    break;
  case "CLW":
    $cCreationUserHTML="Cheryl<br/>Watson";
    break;
  default:
    $cCreationUserHTML=$cCreationUser;
  }

  // Pick up modification date and user
  $cModificationDateNodes=$xpath->query("wlm:ModificationDate",$c);
  if($cModificationDateNodes->length>0){
    $cModificationDate=substr($cModificationDateNodes->item(0)->nodeValue,0,10);
  }else{
    $cModificationDate="";
  }

  $cModificationUserNodes=$xpath->query("wlm:ModificationUser",$c);
  if($cModificationUserNodes->length>0){
    $cModificationUser=$cModificationUserNodes->item(0)->nodeValue;
  }else{
    $cModificationUser="";
  }

  
  switch($cModificationDate){
  case "":
    $cModificationDateHTML="&nbsp";
    break;
  default:
    if($cModificationDate==$cCreationDate){
      $cModificationDateHTML="None";
    }else{
      $cModificationDateHTML=$cModificationDate;
    }
  }

  if($cModificationUser=="" | $cModificationDateHTML=="None"){
    $cModificationUserHTML="&nbsp";
  }else{
  	if($cModificationUser=="CLW"){
	    $cModificationUserHTML="Cheryl<br/>Watson";  	  	
  	}else{
	    $cModificationUserHTML=$cModificationUser;  	
  	}
  }

  // Put out defaults row
  echo "<tr>".cell("<strong>".linkify("SS",$subsys)."</strong><br/>$desc",'left',200).blank_cells(3*$maxClassificationRuleLevel);
  echo cell(href('SC',$defSC));
  echo cell(href('RC',$defRC));
  echo blank_cells(3,'left',75); // Storage Critical, Region Goal, Reporting Attribute
  echo cell($cCreationDateHTML);
  echo cell($cCreationUserHTML);
  echo cell($cModificationDateHTML);
  echo cell($cModificationUserHTML);

  echo "</tr>\n";
  
  // Put out each classification rule for this subsystem  
  do_classification_rules($c,1);
  $seenSCs=array_unique($seenSCs);
  $seenRCs=array_unique($seenRCs);

  // Blank row after the subsystem
  echo "<tr>\n".blank_cells(3+3*$maxClassificationRuleLevel);

  // Flag cells are narrower
  echo blank_cells(3,'center',75);

  echo blank_cells(4)."</tr>";
}

// echo "</tbody>\n";
echo "</table>\n";

if($resGrps->length){
  // List resources groups - as have some

  echo "<a href='#top'><h2 id='resGrps'>Resource Groups</h2></a>\n";
  
  echo "<table class=scrollable border='1'>\n";
  echo "<thead>\n";
  echo "<tr>\n";
  echo "<th>Name</th>\n";
  echo "<th style='min-width: 200px; max-width: 200px;'>Description</th>\n";
  echo "<th style='min-width: 100px; max-width: 100px;'>Type</th>\n";
  echo "<th>Maximum</th>\n";
  echo "<th>Minimum</th>\n";
  echo "<th>Speciality Processor Included</th>\n";
  echo "<th>Created</th>\n";
  echo "<th>User</th>\n";
  echo "<th>Updated</th>\n";
  echo "<th>User</th>\n";
  echo "</tr>\n";
  echo "</thead>\n";

  echo "<tbody>\n";

  foreach($resGrps as $rg){
    $rgName=$xpath->query("wlm:Name",$rg)->item(0)->nodeValue;
    
    // Pick up description
    $rgDesc=$xpath->query("wlm:Description",$rg)->item(0)->nodeValue;
    
    // Pick up type - and tidy up
    $node =$xpath->query("wlm:Type",$rg)->item(0);
    if($node != null){
      $rgType = $node->nodeValue;
    }else{
      $rgType = "&nbsp;";
    }
    
    switch($rgType){
    case "":
      $rgTypeHTML="&nbsp";
      break;
    case "CPUServiceUnits":
    	$rgTypeHTML="CPU Service Units";
    	break;
    case "PercentageLPARShare":
    	$rgTypeHTML="Percentage LPAR Share";
    	break;
    case "NumberCPsTimes100":
      $rgTypeHTML="Percent Of A CP";
      break;
    default:
    	$rgTypeHTML=$rgType;
    }
    
    // Pick up capacity maximum
    $rgCapMaxNodes=$xpath->query("wlm:CapacityMaximum",$rg);
    if($rgCapMaxNodes->length>0){
      $rgCapMax=$rgCapMaxNodes->item(0)->nodeValue;
    }else{
      $rgCapMax="";
    }
    
    // Pick up capacity minimum
    $rgCapMinNodes=$xpath->query("wlm:CapacityMinimum",$rg);
    if($rgCapMinNodes->length>0){
      $rgCapMin=$rgCapMinNodes->item(0)->nodeValue;
    }else{
      $rgCapMin="";
    }
    
    // Pick up creation date and user
    $rgCreationDate=substr($xpath->query("wlm:CreationDate",$rg)->item(0)->nodeValue,0,10);
    $rgCreationUser=$xpath->query("wlm:CreationUser",$rg)->item(0)->nodeValue;
  
    if($rgCreationDate==""){
      $rgCreationDateHTML="&nbsp";
    }else{
      $rgCreationDateHTML=$rgCreationDate;
    }
  
    switch($rgCreationUser){
    case "":
      $rgCreationUserHTML="&nbsp";
      break;
    case "CLW":
      $rgCreationUserHTML="Cheryl<br/>Watson";
      break;
    default:
      $rgCreationUserHTML=$rgCreationUser;
    }
  
    // Pick up modification date and user
    $rgModificationDate=substr($xpath->query("wlm:ModificationDate",$rg)->item(0)->nodeValue,0,10);
    $rgModificationUser=$xpath->query("wlm:ModificationUser",$rg)->item(0)->nodeValue;
    
    switch($rgModificationDate){
    case "":
      $rgModificationDateHTML="&nbsp";
      break;
    default:
      if($rgModificationDate==$rgCreationDate){
        $rgModificationDateHTML="None";
      }else{
        $rgModificationDateHTML=$rgModificationDate;
      }
    }
  
    if($rgModificationUser=="" | $rgModificationDateHTML=="None"){
      $rgModificationUserHTML="&nbsp";
    }else{
    	if($rgModificationUser=="CLW"){
	      $rgModificationUserHTML="Cheryl<br/>Watson";    	
    	}else{    	
	      $rgModificationUserHTML=$rgModificationUser;
    	}
    }
    
    
    // Pick up Include Specialty Processor Consumption - if present
    $rgIncSpecProcConsNodes=$xpath->query("wlm:IncludeSpecialtyProcessorConsumption",$rg);
    if($rgIncSpecProcConsNodes->length>0){
      $rgIncSpecProc=$rgIncSpecProcConsNodes->item(0)->nodeValue;
    }else{
      $rgIncSpecProc="&nbsp;";
    }
    
    $rgUsed=array_search($rgName,$seenRGs);
    if($rgUsed!==false){
      $usedString='';
    }else{
      $usedString='<br/>(Unused)';
    }
  
    echo "<tr>\n";
    echo cell(linkify("RG",$rgName).$usedString)."\n";
    
    if($rgDesc==""){
      $rgDesc="&nbsp";
    }
    echo cell($rgDesc,'left',200)."\n";
  
    echo cell($rgTypeHTML,'left',100)."\n";
    
    if($rgCapMax==""){
      $rgCapMax="&nbsp";
    }
    echo cell($rgCapMax,'right')."\n";
  
    if($rgCapMin==""){
      $rgCapMin="&nbsp";
    }
    echo cell($rgCapMin,'right')."\n";

    echo cell($rgIncSpecProc,'center')."\n";
  
    echo cell($rgCreationDateHTML)."\n";
  
    echo cell($rgCreationUserHTML)."\n";
  
    echo cell($rgModificationDateHTML)."\n";
  
    echo cell($rgModificationUserHTML)."\n";
  
    echo "<tr>\n";
  }
  
  echo "</tbody>\n";
  echo "</table>\n";
}
// List service policies
echo "<a href='#top'><h2 id='srvPols'>Service Policies</h2></a>\n";

echo "<table class=scrollable border='1'>\n";
echo "<thead>\n";
echo "<tr>\n";
echo "<th style='min-width: 100px; max-width: 100px;'>Service Policy</th>\n";
echo "<th style='min-width: 300px; max-width: 300px;'>Description</th>\n";
echo "</tr>\n";
echo "</thead>\n";

foreach($srvPols as $sp){
  $spName=$xpath->query("wlm:Name",$sp)->item(0)->nodeValue;
  
  // Pick up description
  $spDesc=$xpath->query("wlm:Description",$sp)->item(0)->nodeValue;
  
  if($spDesc==""){
    $spDesc="&nbsp";
  }

  echo "<tr>\n";
  echo "<td style='min-width: 100px; max-width: 100px;'><a href='#SP_$spName'>$spName</a></td>\n";
  echo "<td style='min-width: 300px; max-width: 300px;'>$spDesc</td>\n";
  echo "</tr>\n";
}

echo "</table>\n";

foreach($srvPols as $sp){
  $spName=$xpath->query("wlm:Name",$sp)->item(0)->nodeValue;
  
  // Pick up description
  $spDesc=$xpath->query("wlm:Description",$sp)->item(0)->nodeValue;

  echo "<tr>\n";
  
  if($spDesc==""){
    $spDesc="&nbsp";
  }else{
    $spDesc=" - " . $spDesc;
  }

  echo "<a href='#srvPols'><h3 id='SP_$spName'>$spName $spDesc</h3></a>\n";

  // Pick up creation date and user
  $spCreationDate=substr($xpath->query("wlm:CreationDate",$sp)->item(0)->nodeValue,0,10);
  $spCreationUser=$xpath->query("wlm:CreationUser",$sp)->item(0)->nodeValue;

  if($spCreationDate==""){
    $spCreationDateHTML="&nbsp";
  }else{
    $spCreationDateHTML=$spCreationDate;
  }

  switch($spCreationUser){
  case "":
    $spCreationUserHTML="&nbsp";
    break;
  case "CLW":
    $spCreationUserHTML="Cheryl<br/>Watson";
    break;
  default:
    $spCreationUserHTML=$spCreationUser;
  }

  echo "<p>Created: &nbsp;&nbsp;$spCreationDateHTML by $spCreationUserHTML</p>\n";
  
  // Pick up modification date and user
  $spModificationDate=substr($xpath->query("wlm:ModificationDate",$sp)->item(0)->nodeValue,0,10);
  $spModificationUser=$xpath->query("wlm:ModificationUser",$sp)->item(0)->nodeValue;
  
  switch($spModificationDate){
  case "":
    $spModificationDateHTML="";
    break;
  default:
    if($spModificationDate==$spCreationDate){
      $spModificationDateHTML="";
    }else{
      $spModificationDateHTML = $spModificationDate;
    }
  }

  if($spModificationUser=="" | $spModificationDateHTML=="None"){
    $spModificationUserHTML="&nbsp";
  }else{
  	if($spModificationUser=="CLW"){
	    $spModificationUserHTML="Cheryl<br/>Watson";
  	}else{  	
	    $spModificationUserHTML=$spModificationUser;
  	}
  }

  if($spModificationDateHTML != ""){
    echo "<p>Modified: $spModificationDateHTML by $spModificationUserHTML</p>\n";
  }
  

  // Service Class Overrides
  $scOvers=$xpath->query('wlm:ServiceClassOverrides/wlm:ServiceClassOverride',$sp);
  $rgOvers=$xpath->query('wlm:ResourceGroupOverrides/wlm:ResourceGroupOverride',$sp);
  $oversName="";
  $oversType="";
  $oversMin="";
  $oversMax="";

  if($scOvers->length>0){
      echo "<a href='#top'><h4>Service Class Overrides</h4></a>\n";
    echo "<table class=scrollable border='1'>\n";
    echo "<thead>\n";
    echo "<tr>\n";
    echo "<th>Name</th>\n";
    echo "<th>Period</th>\n";
    echo "<th>Duration</th>\n";
    echo "<th>Importance</th>\n";
    echo "<th>Goal Type</th>\n";
    echo "<th>Value</th>\n";
    echo "<th>Resource<br/>Group<br/>Override</th>\n";
    echo "</tr>\n";
    echo "</thead>\n";

    echo "<tbody>\n";
  }

  foreach($scOvers as $scOver){
    $scGoalTypes="";
    $scImportances="";
    $scDurations="";
    $scValues="";
    $scPeriods = "";
    
    $scOverName=$xpath->query("wlm:ServiceClassName",$scOver)->item(0)->nodeValue;
    $oversName.=href('SC',$scOverName)."<br/>";
    echo cell(href("SC", $scOverName))."\n";

    // Using children of Goal node as different types with own node names   
    $scGoals=$xpath->query('wlm:Goal/*',$scOver);

    // Add blank rows for Service Class Name
    $oversName.=str_repeat("&nbsp;<br/>",$scGoals->length);
    $scPeriod = 0;
    foreach($scGoals as $goal){
      $scPeriod++;
      $scPeriods.="$scPeriod<br/>";
      
      // Duration
      $NDL=$xpath->query('wlm:Duration',$goal);
      if($NDL->length>0){
        $scDuration=$NDL->item(0)->nodeValue;
      }else{
        $scDuration="";
      }

      if($scDuration=="") $scDuration="&nbsp;"; 
      $scDurations.="$scDuration<br/>";

      // Importance
      $node = $xpath->query('wlm:Importance',$goal)->item(0);
      if($node != null){
        $scImportance = $node->nodeValue;
      }else{
        $scImportance = "";
      }
      
      $scImportances.="$scImportance<br/>";
      
      // Goal type and value
      $scGoalType=$goal->nodeName;
      switch($scGoalType){
      case "AverageResponseTime":
        $scGoalType="Average";
        $scResponseTime=$xpath->query('wlm:ResponseTime',$goal)->item(0)->nodeValue;
    		$rtHour=floatval(substr($scResponseTime,0,2));
     		$rtMin=floatval(substr($scResponseTime,3,2));
     		$rtSec=floatval(substr($scResponseTime,6));
     		
        $scResponseTimeHTML="";
        if($rtHour!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtHour."h ";
        }
        
        if($rtMin!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtMin."m ";
        }
        
        if($rtSec!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtSec."s ";
        }
        $scValues.="$scResponseTimeHTML<br/>";       
        break;
      case "PercentileResponseTime":
        $scGoalType="Percentile";
        $scPercentile=$xpath->query('wlm:Percentile',$goal)->item(0)->nodeValue;

        $scResponseTime=$xpath->query('wlm:ResponseTime',$goal)->item(0)->nodeValue;
    		$rtHour=floatval(substr($scResponseTime,0,2));
     		$rtMin=floatval(substr($scResponseTime,3,2));
     		$rtSec=floatval(substr($scResponseTime,6));

        $scResponseTimeHTML="";
        if($rtHour!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtHour."h ";
        }
        
        if($rtMin!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtMin."m ";
        }
        
        if($rtSec!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtSec."s ";
        }
        
        $scValues.="$scPercentile% in $scResponseTimeHTML<br/>";       
        break;
      case "Velocity":
        $scLevel=$xpath->query('wlm:Level',$goal)->item(0)->nodeValue;
        $scValues.="$scLevel<br/>";
         
        break;
      case "Discretionary":
        $scValues.="&nbsp;<br/>";
        break;
      default:
        $scValues.="?$scGoalType?<br/>";
      }
      $scGoalTypes.="$scGoalType<br/>";

    }
    
    echo cell($scPeriods, "right")."\n";
	echo cell($scDurations, "right")."\n";
	echo cell($scImportances, "right")."\n";
	echo cell($scGoalTypes)."\n";
	echo cell($scValues, "right")."\n";

    // Put a blank line under each Service Class;
    $scDurations.="&nbsp;<br/>";
    $scImportances.="&nbsp;<br/>";
    $scGoalTypes.="&nbsp;<br/>";
    $scValues.="&nbsp;<br/>";
    
    // Resource group override for this Service class
    $scrgNodes=$xpath->query('wlm:ResourceGroupName',$scOver);
    if($scrgNodes->length > 0){
       echo cell(href("RG", $scrgNodes[0]->nodeValue))."\n";	
    }
    echo "</tr>\n";
  }
   
  if($scOvers->length>0){
    echo "</tbody>\n";
    echo "</table>\n";
  }
  
  // Resource Group Overrides
  $rgOvers=$xpath->query('wlm:ResourceGroupOverrides/wlm:ResourceGroupOverride',$sp);

  if($rgOvers->length>0){
    echo "<a href='#top'><h4>Resource Group Overrides</h4></a>\n";

    echo "<table class=scrollable border='1'>\n";
    echo "<thead>\n";
    echo "<tr>\n";
    echo "<th>Name</th>\n";
    echo "<th>Type</th>\n";
    echo "<th>Minimum</th>\n";
    echo "<th>Maximum</th>\n";
    echo "</tr>\n";
    echo "</thead>\n";

    echo "<tbody>\n";
  }

  foreach($rgOvers as $rgOver){
    echo "<tr>\n";
    $rgOverName=$xpath->query("wlm:ResourceGroupName",$rgOver)->item(0)->nodeValue;
    $oversName ="<a href='#RG_$rgOverName'>$rgOverName</a><br/>";
    echo cell("<a href='#RG_$rgOverName'>$rgOverName</a>")."\n";

    $rgOverType=$xpath->query("wlm:Type",$rgOver)->item(0)->nodeValue;
    switch ($rgOverType) {
    case 'PercentageLPARShare':
      $oversType="Percentage LPAR Share";
      break;
    case 'CPUServiceUnits':
      $oversType="CPU Service Units";
      break;
    case 'NumberCPsTimes100':
	  $oversType="Percentage Of A CP";
	  break;
    default:
      $oversType.="$rgOverType";
      break;
    }

    echo cell($oversType)."\n";
    $node = $xpath->query("wlm:CapacityMinimum",$rgOver)->item(0);
    if($node != null){
      $rgOverCapMin = $node->nodeValue;
      
      if($rgOverCapMin == 0) $rgOverCapMin = "&nbsp;";
    }else{
      $rgOverCapMin= "";
    }
    
    echo cell($rgOverCapMin,'right')."\n";


    $node = $xpath->query("wlm:CapacityMaximum",$rgOver)->item(0);
    if($node != null){
      $rgOverCapMax = $node->nodeValue;

      if($rgOverCapMax == 0) $rgOverCapMax = "&nbsp;";
    }else{
      $rgOverCapMax= "";
    }
    

    echo cell($rgOverCapMax,'right')."\n";
    
    echo "</tr>\n";
  }

  if($rgOvers->length > 0){
    echo "</tbody>\n";
    echo "</table>\n";
  }  
}


// List workloads
echo "<a href='#top'><h2 id='workloads'>Workloads And Service Classes</h2></a>\n";

// Check if CPU Critical specified
$cpuCritsYes=$xpath->query('.//wlm:CPUCritical[text()="Yes"]');
if($cpuCritsYes->length==0){
  echo "<p>CPU Critical was not specified.</p>\n";
  $CPUCriticalCols=0;
}else{
  $CPUCriticalCols=1;
}

// Check if Resource Groups specified
if($resGrps->length==0){
  echo "<p>Resource groups were not specified.</p>\n";
  $resGrpsCols=0;
}else{
  $resGrpsCols=1;
}

// Put out row of links for workloads
echo "<p><strong>Workloads:</strong> ";
foreach($workloads as $wl){
  $wlName=$xpath->query('wlm:Name',$wl)->item(0)->nodeValue;
    echo (href("WL",$wlName)."\n");
}
echo "</p>\n";

echo "<table class=scrollable border='1'>\n";
echo "<thead>\n";
echo "<tr>\n";
echo "<th style='min-width: 200px; max-width: 200px;'>Workload</th>\n";
echo "<th style='min-width: 200px; max-width: 200px;'>Service Class</th>\n";

if($CPUCriticalCols==1){
  echo "<th>CPU<br/>Critical?</th>\n";
}

echo "<th>Duration</th>\n";
echo "<th>Importance</th>\n";
echo "<th>Goal Type</th>\n";
echo "<th>Value</th>\n";

if($resGrpsCols==1){
  echo "<th>Resource<br/>Group</th>\n";
}

if($IOPriorityGroupsEnabled=='Yes'){
  echo "<th>I/O Priority<br/>Group</th>\n";
}

// HonorPriority - if wanted
if($wantHP){
  echo "<th>Honor<br/>Priority</th>\n";
}


echo "<th>Created</th>\n";
echo "<th>User</th>\n";
echo "<th>Updated</th>\n";
echo "<th>User</th>\n";
echo "</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";

foreach($workloads as $wl){
  $wlName=$xpath->query('wlm:Name',$wl)->item(0)->nodeValue;

  $node = $xpath->query('wlm:Description',$wl)->item(0);
  if($node != null){
    $wlDesc = $node->nodeValue;
  }else{
    $wlDesc = "&nbsp";
  }
  
  
  // Pick up creation date and user
  $wlCreationDate=substr($xpath->query("wlm:CreationDate",$wl)->item(0)->nodeValue,0,10);
  $wlCreationUser=$xpath->query("wlm:CreationUser",$wl)->item(0)->nodeValue;

  if($wlCreationDate==""){
    $wlCreationDateHTML="&nbsp";
  }else{
    $wlCreationDateHTML=$wlCreationDate;
  }

  switch($wlCreationUser){
  case "":
    $wlCreationUserHTML="&nbsp";
    break;
  case "CLW":
  $wlCreationUserHTML="Cheryl<br/>Watson";
    break;
  default:
    $wlCreationUserHTML=$wlCreationUser;
  }

  // Pick up modification date and user
  $wlModificationDate=substr($xpath->query("wlm:ModificationDate",$wl)->item(0)->nodeValue,0,10);
  $wlModificationUser=$xpath->query("wlm:ModificationUser",$wl)->item(0)->nodeValue;
  
  switch($wlModificationDate){
  case "":
    $wlModificationDateHTML="&nbsp";
    break;
  default:
    if($wlModificationDate==$wlCreationDate){
      $wlModificationDateHTML="None";
    }else{
      $wlModificationDateHTML=$wlModificationDate;
    }
  }

  if($wlModificationUser=="" | $wlModificationDateHTML=="None"){
    $wlModificationUserHTML="&nbsp";
  }else{
  	if($wlModificationUser=="CLW"){
  	  $wlModificationUserHTML="Cheryl<br/>Watson";
  	}else{
	    $wlModificationUserHTML=$wlModificationUser;
  	}
  }

  echo cell("<strong>".linkify("WL",$wlName)."</strong><br/>$wlDesc","left",200);
  echo blank_cells(5+$CPUCriticalCols+$resGrpsCols)."\n";

  if($IOPriorityGroupsEnabled=='Yes'){
    echo blank_cells(1)."\n";
  }

  // HonorPriority - blank for workload if even wanted
  if($wantHP){
    echo blank_cells(1)."\n";      
  }
    
  echo cell($wlCreationDateHTML)."\n";

  echo cell($wlCreationUserHTML)."\n";

  echo cell($wlModificationDateHTML)."\n";

  echo cell($wlModificationUserHTML)."\n";

  echo "<tr>\n";

  // Service classes  
  $serviceClasses=$xpath->query('wlm:ServiceClasses/wlm:ServiceClass',$wl);
    
  foreach($serviceClasses as $sc){
    $scName=$xpath->query('wlm:Name',$sc)->item(0)->nodeValue;

    $scDesc=$xpath->query('wlm:Description',$sc)->item(0)->nodeValue;

    if($CPUCriticalCols>0){
      $scCPUCritical=$xpath->query('wlm:CPUCritical',$sc)->item(0)->nodeValue;
      if(($scCPUCritical=="No")|($scCPUCritical=="")) $scCPUCritical="&nbsp";
    }

    if($resGrpsCols>0){      
      $NDL=$xpath->query('wlm:ResourceGroupName',$sc);
      if($NDL->length>0){
        $scRGName=$NDL->item(0)->nodeValue;
      }else{
        $scRGName="";
      }
      if($scRGName==""){
        $scRGNameHTML="&nbsp";
      }else{
        $scRGNameHTML=href('RG',$scRGName);
      }
    }

    
    // Using children of Goal node as different types with own node names   
    $scGoals=$xpath->query('wlm:Goal/*',$sc);

    $scGoalTypes="";
    $scImportances="";
    $scDurations="";
    $scValues="";
    foreach($scGoals as $goal){
      
      // Importance
      $NDL=$xpath->query('wlm:Importance',$goal);
      if($NDL->length>0){
        $scImportance=$NDL->item(0)->nodeValue;
      }else{
        $scImportance="";
      }

      $scImportances.="$scImportance<br/>";

      // Duration
      $NDL=$xpath->query('wlm:Duration',$goal);
      if($NDL->length>0){
        $scDuration=$NDL->item(0)->nodeValue;
      }else{
        $scDuration="";
      }

      if($scDuration=="") $scDuration="&nbsp;"; 
      $scDurations.="$scDuration<br/>";

      // Goal type and value
      $scGoalType=$goal->nodeName;
      switch($scGoalType){
      case "AverageResponseTime":
        $scGoalType="Average";
        $scResponseTime=$xpath->query('wlm:ResponseTime',$goal)->item(0)->nodeValue;
    		$rtHour=floatval(substr($scResponseTime,0,2));
     		$rtMin=floatval(substr($scResponseTime,3,2));
     		$rtSec=floatval(substr($scResponseTime,6));

        $scResponseTimeHTML="";
        if($rtHour!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtHour."h ";
        }

        if($rtMin!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtMin."m ";
        }

        if($rtSec!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtSec."s ";
        }
        $scValues.="$scResponseTimeHTML<br/>";
        break;
      case "PercentileResponseTime":
        $scGoalType="Percentile";
        $scPercentile=$xpath->query('wlm:Percentile',$goal)->item(0)->nodeValue;

        $scResponseTime=$xpath->query('wlm:ResponseTime',$goal)->item(0)->nodeValue;
    		$rtHour=floatval(substr($scResponseTime,0,2));
     		$rtMin=floatval(substr($scResponseTime,3,2));
     		$rtSec=floatval(substr($scResponseTime,6));

        $scResponseTimeHTML="";
        if($rtHour!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtHour."h ";
        }
        
        if($rtMin!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtMin."m ";
        }
        
        if($rtSec!=0){
        	$scResponseTimeHTML=$scResponseTimeHTML.$rtSec."s ";
        }
        
        $scValues.="$scPercentile% in $scResponseTimeHTML<br/>";       
        break;
      case "Velocity":
        $scLevel=$xpath->query('wlm:Level',$goal)->item(0)->nodeValue;
        $scValues.="$scLevel<br/>";
         
        break;
      case "Discretionary":
        $scValues.="&nbsp;<br/>";
        break;
      default:
        $scValues.="?$scGoalType?<br/>";
      }
      $scGoalTypes.="$scGoalType<br/>";
    }

    // Maybe I/O Priority Group
    if($IOPriorityGroupsEnabled=='Yes'){
       $scIOPriorityGroup=$xpath->query("wlm:IOPriorityGroup",$sc)->item(0)->nodeValue;
    }

    // Honor Priority - if the column is even in play
    if($wantHP){
      $HonorPriority=$xpath->query("wlm:HonorPriority",$sc)->item(0)->nodeValue;
    }

    // Pick up creation date and user
    $scCreationDate=substr($xpath->query("wlm:CreationDate",$sc)->item(0)->nodeValue,0,10);
    $scCreationUser=$xpath->query("wlm:CreationUser",$sc)->item(0)->nodeValue;

    if($scCreationDate==""){
      $scCreationDateHTML="&nbsp";
    }else{
      $scCreationDateHTML=$scCreationDate;
    }

    switch($scCreationUser){
    case "":
      $scCreationUserHTML="&nbsp";
      break;
    case "CLW":
      $scCreationUserHTML="Cheryl<br/>Watson";
      break;
    default:
      $scCreationUserHTML=$scCreationUser;
    }

    // Pick up modification date and user
    $scModificationDate=substr($xpath->query("wlm:ModificationDate",$sc)->item(0)->nodeValue,0,10);
    $scModificationUser=$xpath->query("wlm:ModificationUser",$sc)->item(0)->nodeValue;
  
    switch($scModificationDate){
    case "":
      $scModificationDateHTML="&nbsp";
      break;
    default:
      if($scModificationDate==$scCreationDate){
        $scModificationDateHTML="None";
      }else{
        $scModificationDateHTML=$scModificationDate;
      }
    }

    if($scModificationUser=="" | $scModificationDateHTML=="None"){
      $scModificationUserHTML="&nbsp";
    }else{
    	if($scModificationUser=="CLW"){
    		$scModificationUserHTML="Cheryl<br/>Watson";
    	}
    	else{
	      $scModificationUserHTML=$scModificationUser;    	
    	}
    }
 
    $scUsed=array_search($scName,$seenSCs);
    if($scUsed!==false){
      $usedString='';
    }else{
      $usedString='<br/>(Unused)';
    }

    echo "<tr>\n";
    echo "$bc\n";
    echo cell("<strong>".linkify('SC',$scName)."</strong><br/>".$scDesc.$usedString,"left",200);
    
    if($cpuCritsYes->length>0){
      echo cell($scCPUCritical)."\n";
    }
    
    echo cell($scDurations,'right')."\n";
    echo cell($scImportances,'right')."\n";
    echo cell($scGoalTypes)."\n";
    echo cell($scValues,'right')."\n";
    
    if($resGrpsCols>0){
      echo cell($scRGNameHTML)."\n";
    }
    
    if($IOPriorityGroupsEnabled=='Yes'){
      echo cell($scIOPriorityGroup)."\n";
    }

    if($wantHP){
      if($HonorPriority=="Default"){
        echo blank_cells(1)."\n";
      }else{
        echo cell($HonorPriority)."\n";
      }
    }

    echo cell($scCreationDateHTML)."\n";
    echo cell($scCreationUserHTML)."\n";
    echo cell($scModificationDateHTML)."\n";
    echo cell($scModificationUserHTML)."\n";

    echo "</tr>\n";
  }

  echo "<tr>\n";
  echo str_repeat($bc,10+$CPUCriticalCols+$resGrpsCols)."\n";
  
  if($wantHP){
    echo blank_cells(1)."\n";
  }

  echo "</tr>\n";
}

// Add SYSTEM and SYSSTC rows - to give links somewhere to go
echo "<tr>\n";
echo cell("<strong>SYSTEM</strong>")."\n";
echo blank_cells(9+$CPUCriticalCols+$resGrpsCols)."\n";

if($wantHP){
  echo blank_cells(1)."\n";
}

echo "</tr>\n";

echo "<tr>\n";
echo $bc."\n";
echo cell(linkify('SC','SYSSTC'))."\n";
echo blank_cells(8+$CPUCriticalCols+$resGrpsCols)."\n";

if($wantHP){
  echo blank_cells(1)."\n";
}

echo "</tr>\n";

echo "<tr>\n";
echo $bc."\n";
echo cell(linkify('SC','SYSTEM'))."\n";
echo blank_cells(8+$CPUCriticalCols+$resGrpsCols)."\n";

if($wantHP){
  echo blank_cells(1)."\n";
}

echo "</tr>\n";

echo "</tbody>\n";
echo "</table>\n";

// List report classes
echo "<a href='#top'><h2 id='rptClasses'>Report Classes</h2></a>\n";

echo "<table class=scrollable border='1'>\n";
echo "<thead>\n";
echo "<tr>\n";
echo "<th>Name</th>\n";
echo "<th style='min-width: 200px; max-width: 200px;'>Description</th>\n";
echo "<th>Created</th>\n";
echo "<th>User</th>\n";
echo "<th>Updated</th>\n";
echo "<th>User</th>\n";
echo "</tr>\n";
echo "</thead>\n";


foreach($rcs as $rc){
  $rcName=$xpath->query('wlm:Name',$rc)->item(0)->nodeValue;

  $rcDescNodes=$xpath->query('wlm:Description',$rc);
  if($rcDescNodes->length>0){
    $rcDesc=$rcDescNodes->item(0)->nodeValue;
  }else{
    $rcDesc="";
  }

    // Pick up creation date and user
    $rcCreationDate=substr($xpath->query("wlm:CreationDate",$rc)->item(0)->nodeValue,0,10);
    $rcCreationUser=$xpath->query("wlm:CreationUser",$rc)->item(0)->nodeValue;

    if($rcCreationDate==""){
      $rcCreationDateHTML="&nbsp";
    }else{
      $rcCreationDateHTML=$rcCreationDate;
    }

    switch($rcCreationUser){
    case "":
      $rcCreationUserHTML="&nbsp";
      break;
    case "CLW":
      $rcCreationUserHTML="Cheryl<br/>Watson";
      break;
    default:
      $rcCreationUserHTML=$rcCreationUser;
    }

    // Pick up modification date and user
    $rcModificationDate=substr($xpath->query("wlm:ModificationDate",$rc)->item(0)->nodeValue,0,10);
    $rcModificationUser=$xpath->query("wlm:ModificationUser",$rc)->item(0)->nodeValue;
  
    switch($rcModificationDate){
    case "":
      $rcModificationDateHTML="&nbsp";
      break;
    default:
      if($rcModificationDate==$rcCreationDate){
        $rcModificationDateHTML="None";
      }else{
        $rcModificationDateHTML=$rcModificationDate;
      }
    }

    if($rcModificationUser=="" | $rcModificationDateHTML=="None"){
      $rcModificationUserHTML="&nbsp";
    }else{
    	if($rcModificationUser=="CLW"){
    		$rcModificationUserHTML="Cheryl<br/>Watson";
    	}else{
	      $rcModificationUserHTML=$rcModificationUser;    	
    	}
    }
 
  $rcUsed=array_search($rcName,$seenRCs);
  if($rcUsed!==false){
    $usedString='';
  }else{
    $usedString='<br/>(Unused)';
  }

  echo "<tr>";
  echo cell(linkify('RC',$rcName).$usedString);
  echo cell($rcDesc,"left",200);
  echo cell($rcCreationDateHTML);
  echo cell($rcCreationUserHTML);
  echo cell($rcModificationDateHTML);
  echo cell($rcModificationUserHTML);
  echo "</tr>\n";
}

echo "</tbody>\n";
echo "</table>\n";

// List application environments
echo "<a href='#top'><h2 id='applEnvs'>Application Environments</h2></a>\n";

if($aes->length > 0){
  echo "<table class=scrollable border='1'>\n";
  echo "<thead>\n";
  echo "<tr>\n";
  echo "<th style='min-width: 200px; max-width: 200px;'>Name</th>\n";
  echo "<th style='min-width: 300px; max-width: 300px;'>Description</th>\n";
  echo "<th>Subsystem<br/>Type</th>\n";
  echo "<th>Address<br/>Space<br/>Limit</th>\n";
  echo "<th>NUMTCB</th>\n";
  echo "<th>Procedure<br/>Name</th>\n";
  echo "<th style='min-width: 300px; max-width: 300px;'>Parameters</th>\n";
  echo "</tr>\n";
  echo "</thead>\n";

  echo "<tbody>\n";

  foreach($aes as $ae){
    $aeName=$xpath->query('wlm:Name',$ae)->item(0)->nodeValue;

    $aeDescNode=$xpath->query('wlm:Description',$ae)->item(0);
    if($aeDescNode != null){
      $aeDesc = $aeDescNode->nodeValue;
    }else{
      $aeDesc = "&nbsp";
    }
  
    $aeSubsysType=$xpath->query('wlm:SubsystemType',$ae)->item(0)->nodeValue;
  
    $aeLimit=$xpath->query('wlm:Limit',$ae)->item(0)->nodeValue;
    switch($aeLimit){
    case "SingleASPerSystem":
      $aeLimitHTML="1 AS per system";
      break;
    case "NoLimit":
      $aeLimitHTML="No limit";
      break;
    default:
      $aeLimitHTML=$aeLimit;
    }
  
    $aeProcNameNode=$xpath->query('wlm:ProcedureName',$ae)->item(0);
    if($aeProcNameNode != null){
      $aeProcName=$aeProcNameNode->nodeValue;
    }else{
      $aeProcName = "&nbsp;";
    }
    
    if($aeProcName==$aeName){
      $aeProcName="=";
      $aeProcAlign='center';
    }else{
      $aeProcAlign='left';
    }
  
    $DNL = $xpath->query('wlm:StartParameter', $ae);
    $NUMTCB = '&nbsp;';
    $DB2SSN ='';
    if($DNL->length > 0){
      /* Get Application Environment start oarameters */
      $aeStartParms = $DNL->item(0)->nodeValue;
    
      /* Parse NUMTCB - if present */
      $NUMTCBpos = strpos($aeStartParms, 'NUMTCB=');
      if($NUMTCBpos !== false){
        $commaPos = strpos($aeStartParms, ',', $NUMTCBpos+7);
        if($commaPos !== false){
          $NUMTCB = strval(intval(substr($aeStartParms, $NUMTCBpos + 7, $commaPos - $NUMTCBpos - 7)));
        } else {
          $NUMTCB = strval(intval(substr($aeStartParms, $NUMTCBpos + 7)));
        }
      }
    
      /* Parse DB2SSN - if present */
      $DB2SSNpos = strpos($aeStartParms, 'DB2SSN=');
      if($DB2SSNpos !== false){
        $commaPos = strpos($aeStartParms, ',', $DB2SSNpos+7);
        if($commaPos !== false){
          $DB2SSN = substr($aeStartParms, $DB2SSNpos + 7, $commaPos - $DB2SSNpos - 7);
        } else {
          $DB2SSN = substr($aeStartParms, $DB2SSNpos + 7);
        }
      
        if($DB2SSN !== "&IWMSSNM"){
          $aeStartParms = str_replace($DB2SSN, "<b>" . $DB2SSN . "</b>", $aeStartParms);
        }
      
      }
    }else{
      $aeStartParms="";
    }
  
    /* Massage Application Environment - if the Db2 subsystem name is present and in the AE name */
    /* Also the description */
    if($DB2SSN !== ""){
      $aeName = str_replace($DB2SSN, "<b>" . $DB2SSN . "</b>", $aeName);
      $aeDesc = str_replace($DB2SSN, "<b>" . $DB2SSN . "</b>", $aeDesc);
    }

    echo "<tr>";
    echo cell(linkify('AE',$aeName),'left',200);
    echo cell($aeDesc,"left",300);
    echo cell($aeSubsysType);
    echo cell($aeLimitHTML);
    echo cell($NUMTCB,'right');
    echo cell($aeProcName,$aeProcAlign);
    echo cell($aeStartParms,"left",300);
    echo "</tr>\n";
    }

  echo "</tbody>\n";
  echo "</table>\n";
}else{
  echo "<p>There are no application environments.</p>\n";
}

if($rs->length > 0){
  // List resources - as have some
  echo "<a href='#top'><h2 id='resources'>Resources</h2></a>\n";

  echo "<table class=scrollable border='1'>\n";
  echo "<thead>\n";
  echo "<tr>\n";
  echo "<th style='min-width: 200px; max-width: 200px;'>Name</th>\n";
  echo "<th style='min-width: 200px; max-width: 200px;'>Description</th>\n";
  echo "</tr>\n";
  echo "</thead>\n";

  echo "<tbody>\n";

  foreach($rs as $r){
    $rName=$xpath->query('wlm:Name',$r)->item(0)->nodeValue;

    $node = $xpath->query('wlm:Description',$r)->item(0);
    if($node != null){
      $rDesc = $node->nodeValue;
    }else{
      $rDesc = "";
    }
    
    $resUsed=array_search($rName,$resources);
    if($resUsed!==false){
      $usedString='';
    }else{
      $usedString='<br/>(Unused)';
    }

    echo "<tr>";
    echo cell(linkify('RS',$rName).$usedString,'left',200);
    echo cell($rDesc,'left',200);
    echo "</tr>\n";
  }

  echo "</tbody>\n";
  echo "</table>\n";
} 
if($ses->length){
  // List scheduling environments - as some defined
  echo "<a href='#top'><h2 id='schEnvs'>Scheduling Environments</h2></a>\n";

  echo "<table class=scrollable border='1'>\n";
  echo "</thead>\n";
  echo "<tr>\n";
  echo "<th style='min-width: 200px; max-width: 200px;'>Name</th>\n";
  echo "<th style='min-width: 200px; max-width: 200px;'>Description</th>\n";
  echo "<th style='min-width: 200px; max-width: 200px;'>Resource</th>\n";
  echo "<th>Required<br/>State</th>\n";
  echo "</tr>\n";
  echo "</thead>\n";
  
  echo "<tbody>\n";


  foreach($ses as $se){
    $seName=$xpath->query('wlm:Name',$se)->item(0)->nodeValue;

    $seDescNode=$xpath->query('wlm:Description',$se)->item(0);
    if($seDescNode != null){
      $seDesc = $seDescNode->nodeValue;
    }else{
      $seDesc = "&nbsp;";
    }
    
    $seRNs=$xpath->query('wlm:ResourceNames/wlm:ResourceName',$se);
    $seRNames="";
    $seRRequiredStates="";
    foreach($seRNs as $seRN){
      $seRName=$xpath->query('wlm:Name',$seRN)->item(0)->nodeValue;
      $seRNames.="<a href='#RS_$seRName'>$seRName</a><br/>";

      $seRRequiredState=$xpath->query('wlm:RequiredState',$seRN)->item(0)->nodeValue;
      $seRRequiredStates.="$seRRequiredState<br/>";
    }
   
    echo "<tr>";
    echo cell(linkify('SE',$seName),'left',200);
    echo cell($seDesc,'left',200);
    echo cell($seRNames,'left',200);
    echo cell($seRRequiredStates);
    echo "</tr>\n";
  }

  echo "</tbody>\n";
  echo "</table>\n";
} 
?>
