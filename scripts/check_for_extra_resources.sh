#!/bin/bash
# run in app/appname/ - see http://www.nexista.org/blog/2008/11/checking-for-extra-resources.html for more info
grep 'query' ./sitemap.xml | awk '{ print $2}' | grep src | sort | uniq | sed s/src=\"data\\/sql\\/// | sed s/\"//  > /tmp/sitemap_sql_file_references.txt
ls -1 data/sql/ > /tmp/sql_files.txt
diff /tmp/sitemap_sql_file_references.txt  /tmp/sql_files.txt
rm  /tmp/sql_files.txt  /tmp/sitemap_sql_file_references.txt
