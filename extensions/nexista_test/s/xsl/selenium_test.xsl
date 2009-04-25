<!--
Program: Nexista
Component: selenium_test.xsl
Copyright: Savonix Corporation
Author: Albert L. Lash, IV
License: LGPL
http://www.gnu.org/licenses

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation; either version 2.1 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with this program; if not, see http://www.gnu.org/licenses
or write to the Free Software Foundation, Inc., 51 Franklin Street,
Fifth Floor, Boston, MA 02110-1301 USA
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" >
  <xsl:output
    method               = "xml"
		indent               = "yes"
    encoding             = "UTF-8"
    omit-xml-declaration = "no"
		doctype-system       = "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
		doctype-public       = "-//W3C//DTD XHTML 1.1//EN"
  />
  <xsl:strip-space elements="*"/>
<xsl:template match="/">
<html>
<head>
<title>Test</title>
</head>
<body>
<table cellpadding="1" cellspacing="1" border="1">
<thead>
<tr><td rowspan="1" colspan="3">Registration Test</td></tr>
</thead>
<tbody>
<tr>
	<td>open</td>
	<td>../index.php?nid=login</td>
	<td></td>
</tr>
</tbody>
</table>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
