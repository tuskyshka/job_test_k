git checkout -b newBranch

git add .

git commit -m 'changes'

git push origin newBranch

git checkout master

git pull origin master

git merge newBranch

git push origin master
