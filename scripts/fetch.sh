#! /bin/bash

#ダウンロードしたファイルが保存されているディレクトリにcdします。
cd /var/www/html/testJob/storage/sheets

#ファイル名は「0500.csv」で終わるはずですが、「0459.csv」、「0501.csv」、「0502.csv」で終わる場合があります。
#したがって、4つの可能なファイル「0500.csv」、「0459.csv」、「0501.csv」、または「0502.csv」をすべてダウンロードします。
file1=$(date +"%Y%m%d%H0500")
file2=$(date +"%Y%m%d%H0501")
file3=$(date +"%Y%m%d%H0459")
file4=$(date +"%Y%m%d%H0502")
files=$(date +"%Y%m%d%H*")

#「expect」コマンドを実行して、ファイルをフェッチし、ファイルを移動します。
expect /var/www/html/testJob/scripts/sftpexp.exp "SSS$file1.csv" "SSS$file2.csv" "SSS$file3.csv" "SSS$files.csv"
