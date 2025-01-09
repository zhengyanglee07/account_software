# How to edit

First and foremost,  
**DONT TOUCH \*.blade.php INSIDE THIS DIRECTORY**  
**DONT TOUCH \*.blade.php INSIDE THIS DIRECTORY**  
**DONT TOUCH \*.blade.php INSIDE THIS DIRECTORY**  

> 18 Aug 2020: Except seller.blade.php, because it hasn't move to mjml yet, I'm lazy :)

You should only edit **\*.mjml** file, and the corresponding blade file will be updated automatically by
issuing the following command on **project root**:

> Note: in case you don't know, `mjml` package already installed for you if you run `npm install` in project root

```
# on windows
.\node_modules\.bin\mjml -w .\resources\views\emailTemplates\orderPayment\your_file.mjml -o .\resources\views\emailTemplates\orderPayment\your_file.blade.php

# on linux/macOS
./node_modules/.bin/mjml -w ./resources/views/emailTemplates/orderPayment/your_file.mjml -o ./resources/views/emailTemplates/orderPayment/your_file.blade.php
```

`-w` in the command above means watch changes made to .mjml, then `-o` means output the changes made.

If you just want one-time compilation from `mjml` to blade, then you can use the following command:

```
# windows
.\node_modules\.bin\mjml input.mjml -o output.blade.php

# linux/macOS
./node_modules/.bin/mjml input.mjml -o output.blade.php
```

Addition:  
If you have installed `mjml` globally with `npm install -g mjml`, then you can omit the `node_modules/.bin` path, e.g.:

```
mjml -w .\resources\views\emailTemplates\orderPayment\your_file.mjml -o .\resources\views\emailTemplates\orderPayment\your_file.blade.php
```

For more, refer to [MJML Documentation](https://mjml.io/documentation/#usage)
