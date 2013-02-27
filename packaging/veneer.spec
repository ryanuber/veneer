%define prefix /usr/share/php
name: veneer
summary: An Experimental API Framework for PHP
version: 0.20
release: 1%{?dist}
buildarch: noarch
license: MIT
source0: %{name}.tar.gz
requires: php
%description
%prep
%setup -n %{name}
%install
%{__mkdir_p} %{buildroot}/%{prefix}/%{name}/{output/handler,exception,http,prototype}
%{__cp} %{name}/*.php %{buildroot}/%{prefix}/%{name}
%{__cp} %{name}/output/*.php %{buildroot}/%{prefix}/%{name}/output
%{__cp} %{name}/output/handler/*.php %{buildroot}/%{prefix}/%{name}/output/handler
%{__cp} %{name}/exception/*.php %{buildroot}/%{prefix}/%{name}/exception
%{__cp} %{name}/http/*.php %{buildroot}/%{prefix}/%{name}/http
%clean
rm -rf %{buildroot}
%files
%defattr(0644,root,root,0755)
%dir %{prefix}/%{name}
%{prefix}/%{name}/*.php
%dir %{prefix}/%{name}/output
%{prefix}/%{name}/output/*.php
%dir %{prefix}/%{name}/output/handler
%{prefix}/%{name}/output/handler/*.php
%dir %{prefix}/%{name}/exception
%{prefix}/%{name}/exception/*.php
%dir %{prefix}/%{name}/http
%{prefix}/%{name}/http/*.php
%changelog
* %(date "+%a %b %d %Y") veneer - %{version}-%{release}
- Automatic build
