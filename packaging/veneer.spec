%define php_includedir %{_datadir}/php
%define unpacked %(%{__tar} -tzf %{SOURCE0} | %{__grep} -E '^(\./)?%{name}(-[^/]+)?/$')

name: veneer
summary: An Experimental API Framework for PHP
version: 0.3
release: 1%{?dist}
buildarch: noarch
license: MIT
source0: %{name}.tar.gz
requires: php

%description
A small, basic API framework written in PHP that has no dependencies other than PHP itself.
It doesn't focus on complex routing, appealing syntax, or making the 'hello world' example
as small as possible, but some basic features include mandatory versioning, route matching,
patterns, and splats, modular output handler layer, input validation, it also includes an
optional stand-alone HTTP server implemented using sockets.

This framework does not aim to be perfect or satisfy every use case imagineable. So why
would anyone want to use it? The veneer framework focuses on versioning, documentation,
validation, and ease of development.

%prep
%setup -n %{unpacked}

%install
%{__mkdir_p} %{buildroot}/%{php_includedir}/%{name}/{output/handler,exception,http,prototype}
%{__cp} %{name}/*.php %{buildroot}/%{php_includedir}/%{name}
%{__cp} %{name}/output/*.php %{buildroot}/%{php_includedir}/%{name}/output
%{__cp} %{name}/output/handler/*.php %{buildroot}/%{php_includedir}/%{name}/output/handler
%{__cp} %{name}/exception/*.php %{buildroot}/%{php_includedir}/%{name}/exception
%{__cp} %{name}/http/*.php %{buildroot}/%{php_includedir}/%{name}/http

%clean
%{__rm} -rf %{buildroot}

%files
%defattr(0644,root,root,0755)
%dir %{php_includedir}/%{name}
%{php_includedir}/%{name}/*.php
%dir %{php_includedir}/%{name}/output
%{php_includedir}/%{name}/output/*.php
%dir %{php_includedir}/%{name}/output/handler
%{php_includedir}/%{name}/output/handler/*.php
%dir %{php_includedir}/%{name}/exception
%{php_includedir}/%{name}/exception/*.php
%dir %{php_includedir}/%{name}/http
%{php_includedir}/%{name}/http/*.php

%changelog
* %(date "+%a %b %d %Y") %{name} - %{version}-%{release}
- Automatic build
