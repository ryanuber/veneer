%define prefix /usr/share/php
name: veneer
summary: An Experimental API Framework for PHP
version: 0.10
release: 1%{?dist}
buildarch: noarch
license: MIT
source0: %{name}.tar.gz
requires: php
%description
%prep
%setup -n %{name}
%install
%{__mkdir_p} %{buildroot}/%{prefix}/%{name}/{encoding,exception,http,prototype}
%{__cp} %{name}/*.php %{buildroot}/%{prefix}/%{name}
%{__cp} %{name}/encoding/*.php %{buildroot}/%{prefix}/%{name}/encoding
%{__cp} %{name}/exception/*.php %{buildroot}/%{prefix}/%{name}/exception
%{__cp} %{name}/http/*.php %{buildroot}/%{prefix}/%{name}/http
%{__cp} %{name}/prototype/*.php %{buildroot}/%{prefix}/%{name}/prototype
%clean
rm -rf %{buildroot}
%files
%defattr(0644,root,root,0755)
%dir %{prefix}/%{name}
%{prefix}/%{name}/*.php
%dir %{prefix}/%{name}/encoding
%{prefix}/%{name}/encoding/*.php
%dir %{prefix}/%{name}/exception
%{prefix}/%{name}/exception/*.php
%dir %{prefix}/%{name}/http
%{prefix}/%{name}/http/*.php
%dir %{prefix}/%{name}/prototype
%{prefix}/%{name}/prototype/*.php
%changelog
* %(date "+%a %b %d %Y") veneer - %{version}-%{release}
- Automatic build
