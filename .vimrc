set nocompatible
set encoding=utf-8
set completeopt-=preview
filetype off
if has("autocmd")
    au BufReadPost * if line("'\"") > 1 && line("'\"") <= line("$") | exe "normal! g'\"" | endif
endif

set rtp+=~/.vim/bundle/Vundle.vim
call vundle#begin()

" vim 插件管理工具
Plugin 'VundleVim/Vundle.vim'

" 美化状态栏 和 主题
Plugin 'vim-airline/vim-airline'
Plugin 'vim-airline/vim-airline-themes'

" 配色方案
Plugin 'crusoexia/vim-monokai'
Plugin 'flazz/vim-colorschemes'

" 显示树形目录
Plugin 'scrooloose/nerdtree'

" python pep8检查
Plugin 'nvie/vim-flake8'

" 代码格式化
"Plugin 'Chiel92/vim-autoformat'
"
"let g:formatdef_harttle = '"astyle --style=attach --pad-oper"'
"let g:formatters_cpp = ['harttle']
"let g:formatters_java = ['harttle']
"noremap <F3> :Autoformat<CR>

call vundle#end()
filetype plugin indent on

syntax enable
syntax on         " 开启语法高亮
set t_Co=256
colorscheme monokai        " murphy 设置配色方案i
set background=dark

set cindent
set hlsearch      " 搜索逐字符高亮
set incsearch
set showmatch     " 高亮显示匹配的括号
set cursorline    " 高亮显示当前 - 行

set nu
set autoindent             " Indent according to previous line.
set expandtab              " Use spaces instead of tabs.
set softtabstop =4         " Tab key indents by 4 spaces.
set shiftwidth  =4         " >> indents by 4 spaces.
set shiftround  

" 禁止显示滚动条
set guioptions-=l
set guioptions-=L
set guioptions-=r
set guioptions-=R
 
" 设置状态栏主题风格
"let g:airline#extensions#tabline#left_sep = ' '
"let g:airline#extensions#tabline#left_alt_sep = '|'
"let g:airline_powerline_fonts=1

"F2开启和关闭树"
map <F2> :NERDTreeToggle<CR>
let NERDTreeChDirMode=1
"显示书签"
let NERDTreeShowBookmarks=1
"设置忽略文件类型"
let NERDTreeIgnore=['\~$', '\.pyc$', '\.swp$']
"窗口大小"
let NERDTreeWinSize=25

let mapleader=','
map <F4> <leader>ci <CR>

" vim-go custom mappings
"au FileType go nmap <Leader>s <Plug>(go-implements)
"au FileType go nmap <Leader>i <Plug>(go-info)
"au FileType go nmap <Leader>gd <Plug>(go-doc)
"au FileType go nmap <Leader>gv <Plug>(go-doc-vertical)
"au FileType go nmap <leader>r <Plug>(go-run)
"au FileType go nmap <leader>b <Plug>(go-build)
"au FileType go nmap <leader>t <Plug>(go-test)
"au FileType go nmap <leader>c <Plug>(go-coverage)
"au FileType go nmap <Leader>ds <Plug>(go-def-split)
"au FileType go nmap <Leader>dv <Plug>(go-def-vertical)
"au FileType go nmap <Leader>dt <Plug>(go-def-tab)
"au FileType go nmap <Leader>e <Plug>(go-rename)

"let g:go_fmt_command = "goimports"

"add yaml stuffs
au! BufNewFile,BufReadPost *.{yaml,yml} set filetype=yaml foldmethod=indent
autocmd FileType yaml setlocal ts=2 sts=2 sw=2 expandtab

" 定义函数AutoSetFileHead，自动插入文件头
autocmd BufNewFile *.sh,*.py exec ":call AutoSetFileHead()"
function! AutoSetFileHead()
    "如果文件类型为.sh文件
    if &filetype == 'sh'
        call setline(1, "\#!/bin/bash")
    endif

    "如果文件类型为python
    if &filetype == 'python'
        " call setline(1, "\#!/usr/bin/env python")
        " call append(1, "\# encoding: utf-8")
        call setline(1, "\# -*- coding: utf-8 -*-")
    endif
    
    normal G
    normal o
    normal o
endfunc

map <F4> ms:call AddAuthor()<cr>'s
function AddAuthor()
    let n=1
    while n < 5
        let line = getline(n)
        if line =~'^\s*\*\s*\S*Last\s*modified\s*:\s*\S*.*'
            call UpdateTitle()
            return
        endif
        let n = n + 1
    endwhile
    call AddTitle()
endfunction

function UpdateTitle()
    normal m'
    execute '/* Last modified\s*:/s@:.*$@\=strftime(": %Y-%m-%d %H:%M")@'
    normal "
    normal mk
    execute '/* Filename\s*:/s@:.*$@\=": ".expand("%:t")@'
    execute "noh"
    normal 'k
    echohl WarningMsg | echo "Successful in updating the copy right." | echohl None
endfunction

function AddTitle()
    call append(2,'"""')
    call append(3,"@author: liangruifeng")
    call append(4,"@email: liang.ruif3ng@gmail.com")
    call append(5,"@filename: ".expand("%:t"))
    call append(6,"@description: ")
    call append(7,"@last modified: ".strftime("%Y-%m-%d %H:%M"))
    call append(8,'"""')
    echohl WarningMsg | echo "Successful in adding the copyright." | echohl None
endfunction

" 折叠代码
set foldmethod=indent
set foldlevel=99
nnoremap <space> za

" YCM 配色
"highlight PMenu ctermfg=15 ctermbg=60 guifg=#2D2E27 guibg=#E8E8E3
"highlight PmenuSel cterm=bold,reverse ctermfg=81 ctermbg=234 gui=bold,reverse guifg=#66d9ef guibg=#272822
highlight PMenu ctermfg=255 ctermbg=240 guifg=#2D2E27 guibg=#E8E8E3
highlight PmenuSel cterm=bold,reverse ctermfg=81 ctermbg=234 gui=bold,reverse guifg=#66d9ef guibg=#272822

"autocmd BufWritePost *.py call Flake8()

" 设置 退出vim后，内容显示在终端屏幕, 可以用于查看和复制
set t_ti= t_te=

" history存储容量
set history=2000

set nonumber



