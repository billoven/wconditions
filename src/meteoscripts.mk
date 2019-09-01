#============================================================================
#                                                                             
# file        : unblbtools.mak                                  
#                                                                             
# Description : UNBLBTOOLS Makefile, marks all UnodeB LoadBuild tools 
#		managed in "src" and copy them in "bin".
#	  	installs a complete version of these tools in install directory
#
#		HELP : clearmake -f unblbtools.mak help 
#
#------------+---------------+------------+------------------------------------
# Date       | author        | Version    | Object                                
#------------+---------------+------------+------------------------------------
# 11/07/2001 | P.STRANART    | 1.0        | Creation                              
#------------+---------------+------------+------------------------------------
# 11/07/2001 | P.STRANART    | V1.0_E03   | ultcruws.pm non copié dans ../bin
#------------+---------------+------------+------------------------------------
# 2004/04/19 | P.STRANART    | V4.1_E01.0 | UCM Migration: Q00864385-01
#------------+---------------+------------+------------------------------------
# 2004/11/09 | C.ALVES       | V4.2_E02.0 | Create new tool to generate Unodeb product
#            |               |            | Add ultprod.pl and ultxml.pm tools
#            |               |            | Q01002504
#------------+---------------+------------+------------------------------------
# 2004/04/19 | P.STRANART    | V4.2_E03.0 | Add new tool to manage ibts metrics
#            |               |            | generation with target metrics => ultcronmetrics
#            |               |            | Q01033933
#------------+---------------+------------+------------------------------------
# 2004/12/06 | C.ALVES       | V4.2_E05.0 | Add new tool to manage ISS automatic
#            |               |            | product deliveries
#            |               |            | Q01040158
#------------+---------------+------------+------------------------------------
# 2005/05/10 | P.STRANART    | V4.2_E07.0 | Suppress 2 tools "ultcrondelivery" and "ultcronmetrics"
#            |               |            | Features of these 2 tools are now included in
#            |               |            | ultcron. Q01133416
#------------+---------------+------------+------------------------------------
# 2005/06/21 | C.ALVES       | V4.2_E07.0 | Add new tool ultrequest to manage iBTS
#            |               |            | delivery request. Q01154527
#------------+---------------+------------+------------------------------------
# 2005-09-01 | C.ALVES       | V4.2_E08.1 | Add new target to install de user 
#            |               |            | documentation on the Web repository
#------------+---------------+------------+------------------------------------
# 2005-11-16 | P.STRANART    | V4.2_E09.0 | Q01236364: Add new tool ultupload
#------------+---------------+------------+------------------------------------
#------------+---------------+------------+------------------------------------
# 2006-03-04 | SAGAR.R       | V4.2_E09.0 | Q01321458: Add new file ulttimestamp
#            |               |            | & Library ultcomlib.pm
#------------+---------------+------------+------------------------------------
#------------+---------------+------------+------------------------------------
# 2006-03-04 | SAGAR.R       | V4.2       | Adding ultsunclient and ultlxserver
#            |               |            | 
#------------+---------------+------------+------------------------------------

#============================================================================
include unblbtoolsinc.mk

FILE_TMP=temp_unblbtools

OBJECTS  = 	$(CC_UNBLBTOOLSBIN)/ultcard \
			$(CC_UNBLBTOOLSBIN)/ultclearmake \
			$(CC_UNBLBTOOLSBIN)/ultprod \
			$(CC_UNBLBTOOLSBIN)/ultdelivery \
			$(CC_UNBLBTOOLSBIN)/ultcron \
			$(CC_UNBLBTOOLSBIN)/ultrshclearmake \
			$(CC_UNBLBTOOLSBIN)/ultrunclearmake \
			$(CC_UNBLBTOOLSBIN)/ultevent \
			$(CC_UNBLBTOOLSBIN)/ultrequest \
			$(CC_UNBLBTOOLSBIN)/ultupload \
			$(CC_UNBLBTOOLSBIN)/ucm_getcardbl \
         $(CC_UNBLBTOOLSBIN)/ulttimestamp \
         $(CC_UNBLBTOOLSBIN)/ultsunclient \
         $(CC_UNBLBTOOLSBIN)/ultlxserver 

LIB   = $(CC_UNBLBTOOLSBIN)/ultcrucws.pm \
		$(CC_UNBLBTOOLSBIN)/ulteventlib.pm \
		$(CC_UNBLBTOOLSBIN)/ultxml.pm \
      $(CC_UNBLBTOOLSBIN)/ultcomlib.pm

DOC	= 	$(CC_UNBLBTOOLSDOC)/ultcard.html \
		$(CC_UNBLBTOOLSDOC)/ultprod.html \
		$(CC_UNBLBTOOLSDOC)/ultdelivery.html \
		$(CC_UNBLBTOOLSDOC)/ultrequest.html \
		$(CC_UNBLBTOOLSDOC)/ultupload.html \
		$(CC_UNBLBTOOLSDOC)/ultevent.html
help :
	@echo "Usage: clearmake -f $(MAKEFILE) [-ukinservwdpUNR] [-J num]"
	@echo "                 [-C compat_mode] [-V | -M] [-O | -T | -F]"
	@echo "                 [-A BOS-file]... "
	@echo "                 [LABEL=value...]"
	@echo "                 [all | unblbtoolsgen | install | installdoc | cleanall | help]"

all : unblbtoolsgen 

unblbtoolsgen : $(OBJECTS) $(LIB)

$(CC_UNBLBTOOLSBIN)/% : $(CC_UNBLBTOOLSSRC)/%.sh
	@echo "Marking of $@ ..."
	@echo "s/\%I\% /$(LABEL) /" > $(FILE_TMP)
	@echo "s/\%M\% /`basename $@` /" >> $(FILE_TMP)
	@echo "s/\%E\% \%U\%/`date`/" >> $(FILE_TMP)
	@sed -f $(FILE_TMP) < $< >$@
	@$(RM) $(RMFLAGS) $(FILE_TMP)

$(CC_UNBLBTOOLSBIN)/% : $(CC_UNBLBTOOLSSRC)/%.tcsh
	@echo "Marking of $@ ..."
	@echo "s/\%I\% /$(LABEL) /" > $(FILE_TMP)
	@echo "s/\%M\% /`basename $@` /" >> $(FILE_TMP)
	@echo "s/\%E\% \%U\%/`date`/" >> $(FILE_TMP)
	@sed -f $(FILE_TMP) < $< >$@
	@$(RM) $(RMFLAGS) $(FILE_TMP)

$(CC_UNBLBTOOLSBIN)/% : $(CC_UNBLBTOOLSSRC)/%.pl
	@echo "Marking of $@ ..."
	@echo "s/\%I\%/$(LABEL)/" > $(FILE_TMP)
	@echo "s/\%M\%/`basename $@`/" >> $(FILE_TMP)
	@echo "s/\%E\% \%U\%/`date`/" >> $(FILE_TMP)
	@sed -f $(FILE_TMP) < $< >$@
	@$(RM) $(RMFLAGS) $(FILE_TMP)

$(CC_UNBLBTOOLSBIN)/%.pm : $(CC_UNBLBTOOLSSRC)/%.pm
	@echo "Perl library marking of $@ ..."
	@echo "s/\%I\%/$(LABEL)/" > $(FILE_TMP)
	@echo "s/\%M\%/`basename $@`/" >> $(FILE_TMP)
	@echo "s/\%E\% \%U\%/`date`/" >> $(FILE_TMP)
	@sed -f $(FILE_TMP) < $< >$@
	@$(RM) $(RMFLAGS) $(FILE_TMP)

install : unblbtoolsgen 
	@echo "Beginning installation of UNBLBTOOLS tools in $(UNBLBTOOLSINSTALLDIR) ..."
	@echo "In 5 seconds ... Type <CTRL C> to abort ...\c :"
	@echo "5\c";sleep 1;echo "4\c";sleep 1;echo "3\c";sleep 1;echo "2\c";sleep 1;echo "1\c";sleep 1;echo "0";
	@$(INSTALL) $(INSTALLDIRFLAGS) $(UNBLBTOOLSINSTALLDIR)
	@for i in $(OBJECTS) ; do\
		$(INSTALL) $(INSTALLFLAGS) -f $(UNBLBTOOLSINSTALLDIR) $$i ;\
		echo " `basename $$i` installed ..." ;\
	done
	@echo "Create symbolic links for $(UNBLBTOOLSINSTALLDIR)/ucm_ultcard"
	@$(RM) $(RMFLAGS) $(UNBLBTOOLSINSTALLDIR)/ucm_ultcard
	@$(LN) $(LNFLAGS) $(UNBLBTOOLSINSTALLDIR)/ultcard $(UNBLBTOOLSINSTALLDIR)/ucm_ultcard
	@echo "Create symbolic links for $(UNBLBTOOLSINSTALLDIR)/ucm_ultcron"
	@$(RM) $(RMFLAGS) $(UNBLBTOOLSINSTALLDIR)/ucm_ultcron
	@$(LN) $(LNFLAGS) $(UNBLBTOOLSINSTALLDIR)/ultcron $(UNBLBTOOLSINSTALLDIR)/ucm_ultcron
	@echo "Create symbolic links for $(UNBLBTOOLSINSTALLDIR)/ucm_ultrunclearmake"
	@$(RM) $(RMFLAGS) $(UNBLBTOOLSINSTALLDIR)/ucm_ultrunclearmake
	@$(LN) $(LNFLAGS) $(UNBLBTOOLSINSTALLDIR)/ultrunclearmake $(UNBLBTOOLSINSTALLDIR)/ucm_ultrunclearmake
	@echo "Install libraries for UNBLBTOOLS tools in $(UNBLBTOOLSINSTALLIBDIR) ..."
	@$(INSTALL) $(INSTALLDIRFLAGS) $(UNBLBTOOLSINSTALLIBDIR)
	@for i in $(LIB) ; do\
		$(INSTALL) $(INSTALLFLAGS) -f $(UNBLBTOOLSINSTALLIBDIR) $$i ;\
		echo " `basename $$i` installed ..." ;\
	done

installdoc :
	@echo "Beginning installation of UNBLBTOOLS tools guides in $(UNBLBTOOLSINSTALLDOCDIR) ..."	
	@for i in $(DOC) ; do\
		$(INSTALL) $(INSTALLFLAGS) -f $(UNBLBTOOLSINSTALLDOCDIR) $$i ;\
		echo " `basename $$i` installed ..." ;\
	done
	
cleanall :
	@echo "Clean $(CC_UNBLBTOOLSBIN) directory tree ..."
	@$(RM) $(RMFLAGS) $(CC_UNBLBTOOLSBIN)/*
