import React, {FC, memo, ReactElement} from 'react'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from "@/components/ui/dialog";
import {Trash2} from "lucide-react";

type ConfirmationModalProps = {
    title: string;
    description: string;
    primaryButton: ReactElement;
    secondaryButton: ReactElement;
    closeCallback: () => void;
}

const ConfirmationModal: FC<ConfirmationModalProps> = ({
    title,
    description,
    primaryButton,
    secondaryButton,
    closeCallback
}) => {
    return (
        <Dialog open onOpenChange={(open) => !open && closeCallback()}>
            <DialogContent className="sm:max-w-105">
                <DialogHeader>
                    <div className="flex items-start gap-3">
                        <div className="flex-size-9 shrink-0 items-center justify-center">
                            <Trash2 className="size-4 text-destructive"/>
                        </div>

                        <div className="flex flex-col gap-1">
                            <DialogTitle className="text-sm font-medium leading-none">
                                {title}
                            </DialogTitle>
                            <DialogDescription className="text-sm text-muted-foreground">
                                {description}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <DialogFooter className="gap-2 sm:gap-2">
                    {secondaryButton}
                    {primaryButton}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

export default memo(ConfirmationModal);
