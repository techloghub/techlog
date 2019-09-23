#!/bin/sh

cd /home/zeyu/Workspace/blog/manager/
git remote set-url origin git@github.com:zeyu203/TechlogManager.git
git pull origin master
git push origin master:master
git remote set-url origin git@techlog.cn:blog/manager.git

cd /home/zeyu/Workspace/blog/techlog/
git remote set-url origin git@github.com:zeyu203/techlog.git
git pull origin master
git push origin master:master
git remote set-url origin git@techlog.cn:blog/techlog.git
