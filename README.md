## 実行方法
1. コンテナを起動
```sh
docker compose up -d
```
2. /app/log/フォルダにcsvに変換したいlogファイルを配置
3. 下記のコマンドでcsvに変換(appNameは任意ですが指定することで対象のアプリのログのみを抽出することができます）
```sh
make conversion_log fileName=2で配置したファイル名 {appName=対象のアプリ名}
// 例
make conversion_log fileName=log.txt appName=com.codmon.bus
```
4. /app/logフォルダにoutput.csvファイルが出力される
![スクリーンショット 2024-10-30 23 12 31](https://github.com/user-attachments/assets/34348036-831a-47e5-a83d-4e6e5c2f9b2d)

## 何をしているのか
- 対象のログファイルの中からエラーレベルがERROR or FATALのものだけを抜き出している
- appNameが指定された場合のみ、appNameが含まれたプロセスIDを持つエラーログを抜き出す

## 目的
- ログ調査を少しでもやりやすくしたい
