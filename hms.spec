%define name hms
%define phpws_dir /var/www/phpwebsite
%define install_dir %{phpws_dir}/mod/hms

Summary:   Housing Management System
Name:      %{name}
Version:   %{version}
Release:   %{release}
License:   GPL
Group:     Development/PHP
URL:       http://phpwebsite.appstate.edu
Source0:   %{name}-%{version}-%{release}.tar.bz2
Source1:   phpwebsite-latest.tar.bz2
Requires:  php >= 5.0.0, php-gd >= 5.0.0
BuildArch: noarch

%description
The Housing Management System

%prep
%setup -n hms

%post
/usr/bin/curl -L -k http://127.0.0.1/apc/clear

%install
mkdir -p "$RPM_BUILD_ROOT%{install_dir}"

# Clean up crap from the repo that doesn't need to be in production
rm -Rf "util"
rm -f "inc/shs0001.wsdl"
rm -f "inc/shs0001.wsdl.testing"
rm -f "build.xml"
rm -f "hms.spec"

# Install the production Banner WSDL file
mkdir -p "$RPM_BUILD_ROOT%{install_dir}/inc"
mv "inc/shs0001.wsdl.prod"\
   "$RPM_BUILD_ROOT%{install_dir}/inc/shs0001.wsdl"

# Install the cron job
#mkdir -p "$RPM_BUILD_ROOT/etc/cron.d"
#mv "inc/hms-cron"\
#   "$RPM_BUILD_ROOT/etc/cron.d/hms-cron"
rm -f "inc/hms-cron"

# Create directory for HMS Archived Reports
mkdir -p "$RPM_BUILD_ROOT%{phpws_dir}/files/hms_reports"

# Put the PDF generator in the right place
mkdir -p "$RPM_BUILD_ROOT/opt"
mv "inc/wkhtmltopdf-i386"\
   "$RPM_BUILD_ROOT/opt/wkhtmltopdf-i386"

# What's left is HMS, copy it to its module directory
cp -r * "$RPM_BUILD_ROOT%{install_dir}"

%clean
rm -rf "$RPM_BUILD_ROOT%{install_dir}"
rm -f "$RPM_BUILD_ROOT/etc/cron.d/hms-cron"
rmdir "$RPM_BUILD_ROOT%{phpws_dir}/files/hms_reports"
rmdir "$RPM_BUILD_ROOT%{phpws_dir}/files"
rm -f "$RPM_BUILD_ROOT/opt/wkhtmltopdf-i386"

%files
%defattr(-,root,root)
%{install_dir}
%attr(-,apache,apache) %{phpws_dir}/files/hms_reports
#/etc/cron.d/hms-cron
%attr(0755,root,root) /opt/wkhtmltopdf-i386

%changelog
* Wed Oct 22 2012 Jeff Tickle <jtickle@tux.appstate.edu>
- Works better with Continuous Integration
* Fri Oct 21 2011 Jeff Tickle <jtickle@tux.appstate.edu>
- Made the phpwebsite install more robust, including the theme
- Added Cron Job, but never tested it so it probably won't work
* Thu Jun  2 2011 Jeff Tickle <jtickle@tux.appstate.edu>
- Added build.xml and hms.spec to the repository, prevented these files from installing
- Added some comments
* Thu Apr 21 2011 Jeff Tickle <jtickle@tux.appstate.edu>
- New spec file for HMS, includes phpWebSite
